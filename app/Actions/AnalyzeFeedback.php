<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enum\FeedbackSentiment;
use App\Enum\FeedbackType;
use App\Enum\UrgencyLevel;
use App\Models\AIAnalysis;
use App\Models\Departament;
use App\Models\Feedback;
use Exception;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class AnalyzeFeedback
{
    /**
     * Handle the feedback analysis action.
     */
    public function handle(Feedback $feedback): AIAnalysis
    {
        try {
            // Skip if already analyzed
            if ($feedback->aIAnalysis()->exists()) {
                Log::info('Feedback already analyzed', [
                    'feedback_id' => $feedback->id,
                ]);

                return $feedback->aIAnalysis()->first();
            }

            // Call OpenAI API to analyze the feedback
            $response = $this->analyzeWithAI($feedback->message ?? $feedback->body);

            // Parse the response
            $analysis = $this->parseAIResponse($response);

            // Save the analysis
            $aiAnalysis = $this->saveFeedbackAnalysis($feedback, $analysis);

            // Update the feedback with the analysis insights
            $this->updateFeedbackWithInsights($feedback, $analysis);

            Log::info('Feedback analyzed successfully', [
                'feedback_id' => $feedback->id,
            ]);

            return $aiAnalysis;
        } catch (Exception $e) {
            Log::error('Error analyzing feedback', [
                'feedback_id' => $feedback->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Call OpenAI API to analyze the feedback.
     */
    private function analyzeWithAI(string $message): array
    {
        try {
            // Set execution time limit for AI operations
            set_time_limit(120); // 2 minutes for AI calls

            $feedback = Feedback::all()->toBase();
            $dataset = Departament::all()->toBase();

            Log::info('Calling OpenAI API for feedback analysis', [
                'message_length' => mb_strlen($message),
                'timeout' => config('openai.analytics_timeout', 120),
            ]);

            $response = $this->makeOpenAICallWithRetry(function () use ($message, $feedback) {
                return OpenAI::chat()->create([
                    'model' => 'gpt-4o',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => "You are an AI assistant for a government feedback system {$feedback}. Your task is to analyze citizen feedback and provide useful insights",
                        ],
                        [
                            'role' => 'user',
                            'content' => "Analyze the following feedback and return a JSON object with these fields:
                                sentiment (positive, negative, or neutral),
                                urgency_level (low, medium, high, critical),
                                feedback_type (complaint, suggestion, question, compliment),
                                tags (array of relevant tags),
                                department (most relevant government department),
                                summary (brief summary in 1-2 sentences).

                                Feedback: {$message}",
                        ],
                    ],
                    'response_format' => [
                        'type' => 'json_object',
                    ],
                    'temperature' => 0.2,
                ]);
            });

            Log::info('OpenAI API response received for feedback analysis', [
                'response_length' => mb_strlen($response->choices[0]->message->content ?? ''),
                'tokens_used' => $response->usage->totalTokens ?? 0,
            ]);

            return json_decode($response->choices[0]->message->content, true);

        } catch (Exception $e) {
            Log::error('OpenAI API call failed in feedback analysis', [
                'error' => $e->getMessage(),
                'error_type' => get_class($e),
                'is_timeout' => str_contains($e->getMessage(), 'execution time') || str_contains($e->getMessage(), 'timeout'),
                'message_length' => mb_strlen($message),
            ]);

            // Return fallback analysis if OpenAI fails
            return $this->getFallbackAnalysis($message);
        }
    }

    /**
     * Parse the AI response to extract structured analysis.
     */
    private function parseAIResponse(array $response): array
    {
        return [
            'sentiment' => $this->mapSentiment($response['sentiment'] ?? 'neutral'),
            'urgency_level' => $this->mapUrgencyLevel($response['urgency_level'] ?? 'low'),
            'feedback_type' => $this->mapFeedbackType($response['feedback_type'] ?? 'question'),
            'suggested_tags' => $response['tags'] ?? [],
            'department' => $response['department'] ?? null,
            'summary' => $response['summary'] ?? null,
        ];
    }

    /**
     * Save the AI analysis to the database.
     */
    private function saveFeedbackAnalysis(Feedback $feedback, array $analysis): AIAnalysis
    {
        return AIAnalysis::create([
            'feedback_id' => $feedback->id,
            'sentiment' => $analysis['sentiment'],
            'suggested_tags' => $analysis['suggested_tags'],
            'analysis_date' => now(),
            'summary' => $analysis['summary'],
            'department_suggestion' => $analysis['department'],
        ]);
    }

    /**
     * Update the feedback with insights from the analysis.
     */
    private function updateFeedbackWithInsights(Feedback $feedback, array $analysis): void
    {
        $feedback->update([
            'sentiment' => $analysis['sentiment'],
            'urgency_level' => $analysis['urgency_level'],
            'feedback_type' => $analysis['feedback_type'],
            'department_assigned' => $analysis['department'],
        ]);
    }

    /**
     * Map the sentiment string to the corresponding enum.
     */
    private function mapSentiment(string $sentiment): FeedbackSentiment
    {
        return match (mb_strtolower($sentiment)) {
            'positive' => FeedbackSentiment::POSITIVE,
            'negative' => FeedbackSentiment::NEGATIVE,
            default => FeedbackSentiment::NEUTRAL,
        };
    }

    /**
     * Map the urgency level string to the corresponding enum.
     */
    private function mapUrgencyLevel(string $urgencyLevel): UrgencyLevel
    {
        return match (mb_strtolower($urgencyLevel)) {
            'high' => UrgencyLevel::HIGH,
            'medium' => UrgencyLevel::MEDIUM,
            'critical' => UrgencyLevel::CRITICAL,
            default => UrgencyLevel::LOW,
        };
    }

    /**
     * Map the feedback type string to the corresponding enum.
     */
    private function mapFeedbackType(string $feedbackType): FeedbackType
    {
        return match (mb_strtolower($feedbackType)) {
            'complaint', 'problem' => FeedbackType::PROBLEM,
            'suggestion' => FeedbackType::SUGGESTION,
            'compliment', 'praise' => FeedbackType::PRAISE,
            default => FeedbackType::SUGGESTION,
        };
    }

    /**
     * Get fallback analysis when OpenAI fails.
     */
    private function getFallbackAnalysis(string $message): array
    {
        // Simple keyword-based analysis as fallback
        $messageLower = mb_strtolower($message);

        // Determine sentiment based on keywords
        $positiveKeywords = ['good', 'great', 'excellent', 'amazing', 'wonderful', 'fantastic', 'love', 'like', 'thank'];
        $negativeKeywords = ['bad', 'terrible', 'awful', 'horrible', 'hate', 'dislike', 'broken', 'problem', 'issue', 'complaint'];

        $sentiment = 'neutral';
        foreach ($positiveKeywords as $keyword) {
            if (str_contains($messageLower, $keyword)) {
                $sentiment = 'positive';
                break;
            }
        }
        foreach ($negativeKeywords as $keyword) {
            if (str_contains($messageLower, $keyword)) {
                $sentiment = 'negative';
                break;
            }
        }

        // Determine urgency based on keywords
        $urgencyLevel = 'medium';
        $urgentKeywords = ['urgent', 'emergency', 'critical', 'immediately', 'asap', 'broken', 'dangerous'];
        foreach ($urgentKeywords as $keyword) {
            if (str_contains($messageLower, $keyword)) {
                $urgencyLevel = 'high';
                break;
            }
        }

        // Determine feedback type
        $feedbackType = 'suggestion';
        if (str_contains($messageLower, 'complain') || str_contains($messageLower, 'problem')) {
            $feedbackType = 'problem';
        } elseif (str_contains($messageLower, 'suggest') || str_contains($messageLower, 'idea')) {
            $feedbackType = 'suggestion';
        } elseif (str_contains($messageLower, 'thank') || str_contains($messageLower, 'great')) {
            $feedbackType = 'praise';
        }

        // Basic department assignment
        $department = 'General Services';
        $departmentKeywords = [
            'Public Works' => ['road', 'street', 'pothole', 'traffic', 'infrastructure'],
            'Parks and Recreation' => ['park', 'playground', 'recreation', 'sports'],
            'Public Safety' => ['police', 'fire', 'emergency', 'safety', 'crime'],
            'Transportation' => ['bus', 'transport', 'transit', 'parking'],
            'Environment' => ['waste', 'recycling', 'pollution', 'environment'],
        ];

        foreach ($departmentKeywords as $dept => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($messageLower, $keyword)) {
                    $department = $dept;
                    break 2;
                }
            }
        }

        return [
            'sentiment' => $sentiment,
            'urgency_level' => $urgencyLevel,
            'feedback_type' => $feedbackType,
            'tags' => ['fallback-analysis'],
            'department' => $department,
            'summary' => mb_substr($message, 0, 100) . (mb_strlen($message) > 100 ? '...' : ''),
        ];
    }

    /**
     * Make OpenAI API call with retry mechanism for timeout handling.
     */
    private function makeOpenAICallWithRetry(callable $callback, int $maxRetries = 2, int $delaySeconds = 3): mixed
    {
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                $attempt++;

                Log::info('Making OpenAI API call for feedback analysis', [
                    'attempt' => $attempt,
                    'max_retries' => $maxRetries,
                    'timeout' => config('openai.analytics_timeout', 120),
                ]);

                return $callback();

            } catch (Exception $e) {
                $isTimeout = str_contains($e->getMessage(), 'execution time') ||
                            str_contains($e->getMessage(), 'timeout') ||
                            str_contains($e->getMessage(), 'cURL error 28');

                Log::warning('OpenAI API call failed for feedback analysis', [
                    'attempt' => $attempt,
                    'max_retries' => $maxRetries,
                    'is_timeout' => $isTimeout,
                    'error' => $e->getMessage(),
                ]);

                // If this is the last attempt or not a timeout, throw the exception
                if ($attempt >= $maxRetries || ! $isTimeout) {
                    throw $e;
                }

                // Wait before retry
                $waitTime = $delaySeconds * $attempt;
                Log::info("Retrying feedback analysis OpenAI call in {$waitTime} seconds...");
                sleep($waitTime);
            }
        }

        throw new Exception('OpenAI API call failed after maximum retries');
    }
}
