<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enum\FeedbackSentiment;
use App\Enum\UrgencyLevel;
use App\Models\Conversation;
use App\Models\Feedback;
use App\Models\Message;
use App\Services\TokenOptimizationService;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

final class ProcessChatMessage
{
    private TokenOptimizationService $tokenService;

    public function __construct(TokenOptimizationService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    public function handle(Conversation $conversation, string $userMessage): array
    {
        // Check if conversation needs compression
        if ($this->tokenService->shouldCompressHistory($conversation)) {
            $this->tokenService->compressOldMessages($conversation);
        }

        // Get feedback context based on user query
        $feedbackContext = $this->getFeedbackContext($userMessage);

        // Build optimized conversation history
        $conversationHistory = $this->buildOptimizedConversationHistory($conversation, $feedbackContext);

        // Add the current user message to the conversation history
        $conversationHistory[] = [
            'role' => 'user',
            'content' => $userMessage,
        ];

        // Generate AI response
        $aiResponse = $this->generateAIResponse($conversationHistory, $userMessage, $feedbackContext);

        // Create user message record
        $userTokens = $this->tokenService->estimateTokens($userMessage);
        $userMessageRecord = Message::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $userMessage,
            'token_count' => $userTokens,
        ]);

        // Create assistant message
        $assistantMessage = Message::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $aiResponse['content'],
            'token_count' => $aiResponse['token_usage'],
            'metadata' => [
                'processing_time' => $aiResponse['processing_time'],
                'model' => $aiResponse['model'],
                'feedback_samples' => count($feedbackContext['samples']),
                'optimization_applied' => $aiResponse['optimization_applied'] ?? false,
            ],
        ]);

        // Update conversation token usage
        $conversation->addTokenUsage($aiResponse['token_usage'] + $userTokens);

        // Update conversation title if it's the first exchange
        if ($conversation->messages()->count() === 2) {
            $conversation->update([
                'title' => $this->generateConversationTitle($userMessage),
            ]);
        }

        return [
            'user_message' => $userMessageRecord,
            'assistant_message' => $assistantMessage,
            'token_usage' => $aiResponse['token_usage'] + $userTokens,
        ];
    }

    private function callOpenAIWithCurl(array $conversationHistory): array
    {
        $apiKey = config('openai.api_key');
        $model = config('chat.openai.model', 'gpt-3.5-turbo');
        $maxTokens = (int) config('chat.openai.max_tokens', 4000);
        $temperature = config('chat.openai.temperature', 0.7);

        $data = [
            'model' => $model,
            'messages' => $conversationHistory,
            'max_tokens' => $maxTokens,
            'temperature' => $temperature,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ]);

        // Disable SSL verification for development
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            throw new Exception('cURL error: ' . curl_error($ch));
        }

        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception('OpenAI API error. HTTP Code: ' . $httpCode . '. Response: ' . $response);
        }

        $responseData = json_decode($response, true);

        return [
            'content' => $responseData['choices'][0]['message']['content'],
            'token_usage' => $responseData['usage']['total_tokens'],
        ];
    }

    private function getFeedbackContext(string $userMessage): array
    {
        $cacheKey = 'chat_feedback_context_' . md5($userMessage);

        return Cache::remember($cacheKey, config('chat.feedback.cache_duration', 3600), function () use ($userMessage) {
            // Extract keywords from user message
            $keywords = $this->extractKeywords($userMessage);

            // Get relevant feedback based on keywords, prioritizing critical issues
            $feedbackQuery = Feedback::with(['user', 'aIAnalysis'])
                ->whereNotNull('sentiment')
                ->orderByRaw("CASE
                    WHEN urgency_level = 'critical' THEN 1
                    WHEN urgency_level = 'high' THEN 2
                    WHEN urgency_level = 'medium' THEN 3
                    ELSE 4
                END")
                ->orderBy('created_at', 'desc')
                ->limit(config('chat.feedback.sample_size', 50));

            // Apply keyword filtering if keywords exist
            if (! empty($keywords)) {
                $feedbackQuery->where(function ($query) use ($keywords) {
                    foreach ($keywords as $keyword) {
                        $query->orWhere('title', 'like', "%{$keyword}%")
                            ->orWhere('body', 'like', "%{$keyword}%")
                            ->orWhere('department_assigned', 'like', "%{$keyword}%")
                            ->orWhere('location', 'like', "%{$keyword}%");
                    }
                });
            }

            $feedbackSamples = $feedbackQuery->get();

            // Get comprehensive statistics
            $totalFeedback = Feedback::count();
            $sentimentDistribution = [
                'positive' => Feedback::where('sentiment', FeedbackSentiment::POSITIVE)->count(),
                'negative' => Feedback::where('sentiment', FeedbackSentiment::NEGATIVE)->count(),
                'neutral' => Feedback::where('sentiment', FeedbackSentiment::NEUTRAL)->count(),
            ];

            $urgencyDistribution = [
                'critical' => Feedback::where('urgency_level', UrgencyLevel::CRITICAL)->count(),
                'high' => Feedback::where('urgency_level', UrgencyLevel::HIGH)->count(),
                'medium' => Feedback::where('urgency_level', UrgencyLevel::MEDIUM)->count(),
                'low' => Feedback::where('urgency_level', UrgencyLevel::LOW)->count(),
            ];

            return [
                'samples' => $feedbackSamples,
                'statistics' => [
                    'total_feedback' => $totalFeedback,
                    'sentiment_distribution' => $sentimentDistribution,
                    'urgency_distribution' => $urgencyDistribution,
                ],
                'keywords' => $keywords,
            ];
        });
    }

    private function extractKeywords(string $text): array
    {
        $commonWords = ['the', 'is', 'at', 'which', 'on', 'and', 'a', 'to', 'are', 'as', 'was', 'were', 'been', 'be', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'should', 'could', 'can', 'may', 'might', 'must', 'shall', 'about', 'what', 'where', 'when', 'why', 'how', 'who', 'with', 'from', 'for', 'of', 'in', 'by', 'me', 'show', 'tell', 'give', 'get', 'latest', 'recent', 'new', 'old'];

        $words = str_word_count(mb_strtolower($text), 1);
        $keywords = array_filter($words, function ($word) use ($commonWords) {
            return mb_strlen($word) > 3 && ! in_array($word, $commonWords);
        });

        return array_values(array_unique($keywords));
    }

    private function buildOptimizedConversationHistory(Conversation $conversation, array $feedbackContext): array
    {
        $optimizedMessages = $this->tokenService->optimizeConversationHistory($conversation);

        $history = [];
        $tokenCount = 0;
        $maxTokens = config('chat.conversation.max_context_tokens', 8000);

        // Add system message with feedback context
        $systemMessage = $this->buildSystemMessage($feedbackContext);
        $systemTokens = $this->tokenService->estimateTokens($systemMessage);

        if ($tokenCount + $systemTokens < $maxTokens) {
            $history[] = [
                'role' => 'system',
                'content' => $systemMessage,
            ];
            $tokenCount += $systemTokens;
        }

        // Add optimized conversation messages (excluding the current user message that hasn't been saved yet)
        foreach ($optimizedMessages as $message) {
            if ($tokenCount + $message['token_count'] > $maxTokens) {
                break;
            }

            $history[] = [
                'role' => $message['role'],
                'content' => $message['content'],
            ];
            $tokenCount += $message['token_count'];
        }

        return $history;
    }

    private function buildSystemMessage(array $feedbackContext): string
    {
        $stats = $feedbackContext['statistics'];
        $sampleSummary = $this->buildFeedbackSummary($feedbackContext['samples']);

        return "You are PulseGov AI, an intelligent assistant for government officials analyzing citizen feedback. You have access to real-time citizen feedback data and must provide specific, data-driven insights based on this information.

CURRENT FEEDBACK DATABASE SUMMARY:
- Total Feedback Reports: {$stats['total_feedback']}
- Sentiment Distribution: {$stats['sentiment_distribution']['positive']} positive, {$stats['sentiment_distribution']['negative']} negative, {$stats['sentiment_distribution']['neutral']} neutral
- Urgency Distribution: {$stats['urgency_distribution']['critical']} critical, {$stats['urgency_distribution']['high']} high, {$stats['urgency_distribution']['medium']} medium, {$stats['urgency_distribution']['low']} low

RECENT RELEVANT FEEDBACK SAMPLES:
{$sampleSummary}

IMPORTANT INSTRUCTIONS:
1. Always use the provided feedback data to answer questions
2. When asked about total feedback, refer to the exact number: {$stats['total_feedback']}
3. For critical issues, prioritize feedback marked as 'critical' or 'high' urgency
4. Provide specific examples from the feedback samples when relevant
5. Give actionable recommendations based on the data
6. Never give generic responses - always reference the actual data provided
7. When mentioning specific feedback, remind officials they can click the provided URLs to view full details
8. Include relevant URLs in your responses when discussing specific feedback items

Answer all questions using the specific data provided above. Focus on actionable insights and concrete examples from the citizen feedback. When referring to specific feedback, mention that officials can access the full details by clicking the provided links.";
    }

    private function buildFeedbackSummary(object $samples): string
    {
        if ($samples->isEmpty()) {
            return 'No specific feedback samples available for this query.';
        }

        $summary = [];
        $criticalIssues = [];
        $highPriorityIssues = [];
        $regularIssues = [];

        foreach ($samples as $feedback) {
            $urgencyLevel = $feedback->urgency_level?->value ?? 'unknown';
            $sentiment = $feedback->sentiment?->value ?? 'neutral';
            $feedbackUrl = url("/feedback/{$feedback->id}");
            $feedbackLine = "- \"{$feedback->title}\" (Location: {$feedback->location}, Urgency: {$urgencyLevel}, Sentiment: {$sentiment}) - View details: {$feedbackUrl}";

            if ($urgencyLevel === 'critical') {
                $criticalIssues[] = $feedbackLine;
            } elseif ($urgencyLevel === 'high') {
                $highPriorityIssues[] = $feedbackLine;
            } else {
                $regularIssues[] = $feedbackLine;
            }
        }

        // Build summary starting with most critical
        if (! empty($criticalIssues)) {
            $summary[] = 'CRITICAL ISSUES (requiring immediate attention):';
            $summary = array_merge($summary, array_slice($criticalIssues, 0, 3));
        }

        if (! empty($highPriorityIssues)) {
            $summary[] = "\nHIGH PRIORITY ISSUES:";
            $summary = array_merge($summary, array_slice($highPriorityIssues, 0, 3));
        }

        if (! empty($regularIssues) && count($summary) < 8) {
            $summary[] = "\nOTHER FEEDBACK:";
            $summary = array_merge($summary, array_slice($regularIssues, 0, 2));
        }

        return implode("\n", $summary);
    }

    private function generateAIResponse(array $conversationHistory, string $userMessage, array $feedbackContext): array
    {
        $startTime = microtime(true);
        $optimizationApplied = false;

        try {
            Log::info('Generating AI response for chat', [
                'user_message_length' => mb_strlen($userMessage),
                'context_samples' => count($feedbackContext['samples']),
                'conversation_history_length' => count($conversationHistory),
            ]);

            $openAIResponse = $this->callOpenAIWithCurl($conversationHistory);

            $processingTime = microtime(true) - $startTime;
            $content = $openAIResponse['content'];
            $tokenUsage = $openAIResponse['token_usage'];

            Log::info('AI response generated successfully', [
                'processing_time' => $processingTime,
                'token_usage' => $tokenUsage,
                'response_length' => mb_strlen($content),
                'optimization_applied' => $optimizationApplied,
            ]);

            return [
                'content' => $content,
                'token_usage' => $tokenUsage,
                'processing_time' => $processingTime,
                'model' => config('chat.openai.model', 'gpt-4o'),
                'optimization_applied' => $optimizationApplied,
            ];

        } catch (Exception $e) {
            Log::error('AI response generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'processing_time' => microtime(true) - $startTime,
            ]);

            return [
                'content' => config('chat.system_prompts.error_message') . ' Debug: ' . $e->getMessage(),
                'token_usage' => 0,
                'processing_time' => microtime(true) - $startTime,
                'model' => 'fallback',
                'optimization_applied' => false,
            ];
        }
    }

    private function generateConversationTitle(string $userMessage): string
    {
        $words = explode(' ', $userMessage);
        $title = implode(' ', array_slice($words, 0, 6));

        if (mb_strlen($title) > 50) {
            $title = mb_substr($title, 0, 47) . '...';
        }

        return $title;
    }
}
