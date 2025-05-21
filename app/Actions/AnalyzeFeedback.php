<?php

namespace App\Actions;

use App\Enum\FeedbackSentiment;
use App\Enum\FeedbackType;
use App\Enum\UrgencyLevel;
use App\Models\AIAnalysis;
use App\Models\Feedback;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class AnalyzeFeedback
{
    /**
     * Handle the feedback analysis action.
     *
     * @param Feedback $feedback
     * @return AIAnalysis
     */
    public function handle(Feedback $feedback): AIAnalysis
    {
        try {
            // Skip if already analyzed
            if ($feedback->aIAnalysis()->exists()) {
                Log::info('Feedback already analyzed', [
                    'feedback_id' => $feedback->id,
                ]);
                return $feedback->aIAnalysis;
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
        } catch (\Exception $e) {
            Log::error('Error analyzing feedback', [
                'feedback_id' => $feedback->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Call OpenAI API to analyze the feedback.
     *
     * @param string $message
     * @return array
     */
    private function analyzeWithAI(string $message): array
    {
        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an AI assistant for a government feedback system. Your task is to analyze citizen feedback and provide useful insights.'
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

                        Feedback: {$message}"
                ]
            ],
            'response_format' => [
                'type' => 'json_object'
            ],
            'temperature' => 0.2,
        ]);

        return json_decode($response->choices[0]->message->content, true);
    }

    /**
     * Parse the AI response to extract structured analysis.
     *
     * @param array $response
     * @return array
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
     *
     * @param Feedback $feedback
     * @param array $analysis
     * @return AIAnalysis
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
     *
     * @param Feedback $feedback
     * @param array $analysis
     * @return void
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
     *
     * @param string $sentiment
     * @return FeedbackSentiment
     */
    private function mapSentiment(string $sentiment): FeedbackSentiment
    {
        return match (strtolower($sentiment)) {
            'positive' => FeedbackSentiment::POSITIVE,
            'negative' => FeedbackSentiment::NEGATIVE,
            default => FeedbackSentiment::NEUTRAL,
        };
    }

    /**
     * Map the urgency level string to the corresponding enum.
     *
     * @param string $urgencyLevel
     * @return UrgencyLevel
     */
    private function mapUrgencyLevel(string $urgencyLevel): UrgencyLevel
    {
        return match (strtolower($urgencyLevel)) {
            'high' => UrgencyLevel::HIGH,
            'medium' => UrgencyLevel::MEDIUM,
            'critical' => UrgencyLevel::CRITICAL,
            default => UrgencyLevel::LOW,
        };
    }

    /**
     * Map the feedback type string to the corresponding enum.
     *
     * @param string $feedbackType
     * @return FeedbackType
     */
    private function mapFeedbackType(string $feedbackType): FeedbackType
    {
        return match (strtolower($feedbackType)) {
            'complaint' => FeedbackType::COMPLAINT,
            'suggestion' => FeedbackType::SUGGESTION,
            'compliment' => FeedbackType::COMPLIMENT,
            default => FeedbackType::QUESTION,
        };
    }
}
