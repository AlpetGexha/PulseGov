<?php

namespace App\Actions;

use App\Enum\FeedbackSentiment;
use App\Enum\FeedbackType;
use App\Enum\UrgencyLevel;
use App\Models\AIAnalysis;
use App\Models\Feedback;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StreamAnalyzeFeedback extends AnalyzeFeedback
{
    /**
     * Handle the feedback analysis action with streaming output.
     *
     * @param Feedback $feedback
     * @return StreamedResponse
     */
    public function handleStream(Feedback $feedback): StreamedResponse
    {
        return new StreamedResponse(function () use ($feedback) {
            try {
                // Skip if already analyzed
                if ($feedback->aIAnalysis()->exists()) {
                    echo "Feedback already analyzed (ID: {$feedback->id})\n";
                    flush();
                    return;
                }

                echo "Starting feedback analysis for ID: {$feedback->id}...\n";
                flush();

                // Call OpenAI API to analyze the feedback with streaming
                echo "Analyzing feedback content...\n";
                flush();

                $response = $this->analyzeWithAIStream($feedback->message ?? $feedback->body);

                echo "\nCompleting analysis...\n";
                flush();

                // Parse the response
                $analysis = $this->parseAIResponse($response);

                echo "Saving analysis results...\n";
                flush();

                // Save the analysis
                $aiAnalysis = $this->saveFeedbackAnalysis($feedback, $analysis);

                // Update the feedback with the analysis insights
                $this->updateFeedbackWithInsights($feedback, $analysis);

                echo "\nFeedback analysis completed successfully:\n";
                echo "- Sentiment: {$analysis['sentiment']->value}\n";
                echo "- Urgency: {$analysis['urgency_level']->value}\n";
                echo "- Type: {$analysis['feedback_type']->value}\n";
                echo "- Department: {$analysis['department']}\n";
                echo "- Summary: {$analysis['summary']}\n";
                flush();

            } catch (\Exception $e) {
                echo "Error analyzing feedback: {$e->getMessage()}\n";
                flush();

                Log::error('Error in streaming feedback analysis', [
                    'feedback_id' => $feedback->id,
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }

    /**
     * Call OpenAI API to analyze the feedback with true streaming output.
     * This method is an alternative that shows the actual AI thinking in real-time,
     * but note that it won't return structured JSON data due to streaming limitations.
     *
     * @param string $message
     * @return void
     */
    public function analyzeWithRealTimeStream(Feedback $feedback): StreamedResponse
    {
        return new StreamedResponse(function () use ($feedback) {
            try {
                $message = $feedback->message ?? $feedback->body;

                echo "Starting real-time streaming analysis of feedback #{$feedback->id}\n";
                echo "------------------------------------------------------\n\n";
                flush();

                $stream = \OpenAI\Laravel\Facades\OpenAI::chat()->createStreamed([
                    'model' => 'gpt-4o',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => "You are an AI assistant for a government feedback system. Analyze the feedback and provide insights directly in real-time. Include your thought process as you analyze the sentiment, urgency, feedback type, relevant tags, department suggestions, and a summary.",
                        ],
                        [
                            'role' => 'user',
                            'content' => "Analyze the following feedback in detail:\n\n{$message}"
                        ]
                    ],
                    'temperature' => 0.3,
                ]);

                foreach ($stream as $response) {
                    if (isset($response->choices[0]->delta->content)) {
                        echo $response->choices[0]->delta->content;
                        flush();
                    }
                }

                echo "\n\n------------------------------------------------------\n";
                echo "Analysis complete. Note: This streaming analysis is for viewing purposes only and isn't saved to the database.\n";
                flush();

            } catch (\Exception $e) {
                echo "Error in real-time streaming analysis: {$e->getMessage()}\n";
                flush();

                Log::error('Error in real-time streaming feedback analysis', [
                    'feedback_id' => $feedback->id,
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }

    /**
     * Call OpenAI API to analyze the feedback with streaming output.
     *
     * @param string $message
     * @return array
     */    private function analyzeWithAIStream(string $message): array
    {
        $feedback = Feedback::all()->toBase();
        $dataset = \App\Models\Departament::all()->toBase();

        echo "Preparing analysis context...\n";
        echo "Processing feedback message...\n";
        flush();

        // First, we need to make the API call to get structured data
        // OpenAI streaming doesn't work well with structured data responses,
        // so we'll stream the status updates instead

        $systemPrompt = "You are an AI assistant for a government feedback system. Your task is to analyze citizen feedback and provide useful insights";
        $userPrompt = "Analyze the following feedback and return a JSON object with these fields:
            sentiment (positive, negative, or neutral),
            urgency_level (low, medium, high, critical),
            feedback_type (complaint, suggestion, question, compliment),
            tags (array of relevant tags),
            department (most relevant government department),
            summary (brief summary in 1-2 sentences).

            Feedback: {$message}";

        echo "Sending to AI for analysis";

        // Visual progress indicator
        for ($i = 0; $i < 5; $i++) {
            echo ".";
            flush();
            usleep(300000); // 300ms delay
        }

        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $systemPrompt,
                ],
                [
                    'role' => 'user',
                    'content' => $userPrompt
                ]
            ],
            'response_format' => [
                'type' => 'json_object'
            ],
            'temperature' => 0.2,
        ]);

        return json_decode($response->choices[0]->message->content, true);
    }
}
