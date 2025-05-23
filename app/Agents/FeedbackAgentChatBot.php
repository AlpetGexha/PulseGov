<?php

namespace App\Agents;

use NeuronAI\Agent;
use NeuronAI\SystemPrompt;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\OpenAI\OpenAI;
use App\Models\Feedback;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use App\Enum\FeedbackType;
use App\Enum\FeedbackSentiment;
use App\Enum\UrgencyLevel;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use OpenAI\Laravel\Facades\OpenAI as LaravelOpenAI;

class FeedbackAgentChatBot extends Agent
{
    protected Collection $feedbackData;
    protected array $parsedData = [];
    protected string $queryType = '';
    protected string $location = '';
    protected string $issueType = '';
    protected string $timeFrame = '';

    /**
     * Ask the AI model a question and get a response
     *
     * @param string $query The query to ask
     * @param array $options Additional options like context
     * @return string The AI response
     */
    protected function ask(string $query, array $options = []): string
    {
        $systemPrompt = $this->instructions();
        $userPrompt = $query;

        if (isset($options['context'])) {
            $userPrompt = $options['context'] . "\n\n" . $query;
        }

        $response = LaravelOpenAI::chat()->create([
            'model' => 'gpt-4o',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => (string) $systemPrompt,
                ],
                [
                    'role' => 'user',
                    'content' => $userPrompt
                ]
            ],
            'temperature' => 0.5,
        ]);

        return $response->choices[0]->message->content;
    }

    /**
     * Ask the AI model a question and get a streaming response
     *
     * @param string $query The query to ask
     * @param array $options Additional options like context
     * @return \Generator The streaming AI response
     */
    protected function askStream(string $query, array $options = []): \Generator
    {
        $systemPrompt = $this->instructions();
        $userPrompt = $query;

        if (isset($options['context'])) {
            $userPrompt = $options['context'] . "\n\n" . $query;
        }

        $stream = LaravelOpenAI::chat()->createStreamed([
            'model' => 'gpt-4o',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => (string) $systemPrompt,
                ],
                [
                    'role' => 'user',
                    'content' => $userPrompt
                ]
            ],
            'temperature' => 0.5,
        ]);

        foreach ($stream as $response) {
            if (isset($response->choices[0]->delta->content)) {
                yield $response->choices[0]->delta->content;
            }
        }
    }

    public function provider(): AIProviderInterface
    {
        return new OpenAI(
            config('service.OPENAI_API_KEY'),
            model: 'gpt-4o',
        );
    }

    /**
     * Process the user query and fetch relevant data before response generation
     *
     * @param string $query The user query
     * @return string The response
     */
    public function processQuery(string $query): string
    {
        try {
            // Parse the query to extract key parameters
            $this->parseUserQuery($query);

            // Fetch feedback data based on parsed parameters
            $this->fetchFeedbackData();

            // Process the data to extract insights
            $this->processData();

            // Generate the response using the AI
            return $this->generateResponse($query);
        } catch (\Exception $e) {
            Log::error('Error processing feedback query', [
                'query' => $query,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return "Sorry, I encountered an error while retrieving feedback data: {$e->getMessage()}";
        }
    }

    /**
     * Process the user query and fetch relevant data with streaming response
     *
     * @param string $query The user query
     * @return StreamedResponse The streamed response
     */
    public function processQueryStream(string $query): StreamedResponse
    {
        return new StreamedResponse(function () use ($query) {
            try {
                // Parse the query to extract key parameters
                $this->parseUserQuery($query);
                echo "Analyzing query...\n";
                flush();

                // Fetch feedback data based on parsed parameters
                $this->fetchFeedbackData();
                echo "Retrieved " . $this->feedbackData->count() . " feedback records...\n";
                flush();

                // Process the data to extract insights
                $this->processData();
                echo "Processing feedback data...\n\n";
                flush();

                // Generate the response using the AI with streaming
                $stream = $this->generateResponseStream($query);
                foreach ($stream as $chunk) {
                    echo $chunk;
                    flush();
                }
            } catch (\Exception $e) {
                Log::error('Error processing feedback query stream', [
                    'query' => $query,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                echo "Sorry, I encountered an error while retrieving feedback data: {$e->getMessage()}";
                flush();
            }
        });
    }

    /**
     * Parse the user query to extract location, issue type, and timeframe
     *
     * @param string $query The user query
     */
    protected function parseUserQuery(string $query): void
    {
        // Extract location (simple implementation - can be enhanced with NLP)
        $locationMatches = [];
        preg_match('/from\s+([A-Za-z\s]+?)(?:\s+related|\s+about|\s+concerning|$)/', $query, $locationMatches);
        $this->location = $locationMatches[1] ?? '';

        // Extract issue type
        $issueTypes = [
            'road' => ['road', 'street', 'pothole', 'pavement'],
            'water' => ['water', 'pipe', 'leak', 'plumbing'],
            'electricity' => ['electricity', 'power', 'outage', 'blackout'],
            'waste' => ['waste', 'trash', 'garbage', 'recycling'],
            'safety' => ['safety', 'crime', 'security', 'police'],
            'education' => ['education', 'school', 'teacher', 'student'],
            'health' => ['health', 'hospital', 'clinic', 'doctor']
        ];

        $this->issueType = '';
        foreach ($issueTypes as $type => $keywords) {
            foreach ($keywords as $keyword) {
                if (stripos($query, $keyword) !== false) {
                    $this->issueType = $type;
                    break 2;
                }
            }
        }

        // Extract timeframe
        $timeMatches = [];
        preg_match('/(latest|recent|past\s+\d+\s+days|this\s+week|this\s+month|last\s+\d+\s+weeks)/', $query, $timeMatches);
        $this->timeFrame = $timeMatches[0] ?? 'latest';

        // Determine query type
        if (stripos($query, 'trend') !== false || stripos($query, 'pattern') !== false) {
            $this->queryType = 'trend';
        } elseif (stripos($query, 'count') !== false || stripos($query, 'how many') !== false) {
            $this->queryType = 'count';
        } elseif (stripos($query, 'urgent') !== false || stripos($query, 'priority') !== false) {
            $this->queryType = 'urgent';
        } else {
            $this->queryType = 'general';
        }
    }

    /**
     * Fetch relevant feedback data from database
     */
    protected function fetchFeedbackData(): void
    {
        $query = Feedback::with(['aIAnalysis', 'comments', 'votes'])
            ->orderBy('created_at', 'desc');

        // Apply location filter if provided
        if (!empty($this->location)) {
            $query->where(function($q) {
                $q->where('location', 'like', '%' . $this->location . '%')
                  ->orWhere('body', 'like', '%' . $this->location . '%')
                  ->orWhere('title', 'like', '%' . $this->location . '%');
            });
        }

        // Apply issue type filter if provided
        if (!empty($this->issueType)) {
            $query->where(function($q) {
                $q->where('body', 'like', '%' . $this->issueType . '%')
                  ->orWhere('title', 'like', '%' . $this->issueType . '%');
            });
        }

        // Apply timeframe filter
        switch ($this->timeFrame) {
            case 'this week':
                $query->where('created_at', '>=', now()->startOfWeek());
                break;
            case 'this month':
                $query->where('created_at', '>=', now()->startOfMonth());
                break;
            case (preg_match('/past\s+(\d+)\s+days/', $this->timeFrame, $matches) ? true : false):
                $days = $matches[1];
                $query->where('created_at', '>=', now()->subDays($days));
                break;
            case (preg_match('/last\s+(\d+)\s+weeks/', $this->timeFrame, $matches) ? true : false):
                $weeks = $matches[1];
                $query->where('created_at', '>=', now()->subWeeks($weeks));
                break;
            default:
                // Default to last 30 days
                $query->where('created_at', '>=', now()->subDays(30));
        }

        // Limit to a reasonable number of results
        $this->feedbackData = $query->limit(50)->get();
    }

    /**
     * Process the feedback data to extract insights
     */
    protected function processData(): void
    {
        if ($this->feedbackData->isEmpty()) {
            $this->parsedData = [];
            return;
        }

        // Initialize the parsed data structure
        $this->parsedData = [
            'uniqueReports' => $this->feedbackData->count(),
            'firstReport' => $this->feedbackData->sortBy('created_at')->first()->created_at,
            'latestReport' => $this->feedbackData->sortByDesc('created_at')->first()->created_at,
            'sentimentCounts' => [
                'positive' => 0,
                'negative' => 0,
                'neutral' => 0
            ],
            'urgencyLevels' => [
                'high' => 0,
                'medium' => 0,
                'low' => 0,
                'critical' => 0
            ],
            'departmentSuggestions' => [],
            'locationCoordinates' => [],
            'tags' => [],
            'repetitionCount' => 0,
            'commentCount' => 0
        ];

        // Calculate sentiment distribution
        foreach ($this->feedbackData as $feedback) {
            // Collect sentiment data
            if ($feedback->sentiment) {
                $sentimentValue = $feedback->sentiment->value;
                $this->parsedData['sentimentCounts'][$sentimentValue]++;
            }

            // Collect urgency level data
            if ($feedback->urgency_level) {
                $urgencyValue = $feedback->urgency_level->value;
                $this->parsedData['urgencyLevels'][$urgencyValue]++;
            }

            // Collect department suggestions
            if ($feedback->department_assigned) {
                if (!isset($this->parsedData['departmentSuggestions'][$feedback->department_assigned])) {
                    $this->parsedData['departmentSuggestions'][$feedback->department_assigned] = 0;
                }
                $this->parsedData['departmentSuggestions'][$feedback->department_assigned]++;
            }

            // Collect tags from AI Analysis
            if ($feedback->aIAnalysis && !empty($feedback->aIAnalysis->suggested_tags)) {
                foreach ($feedback->aIAnalysis->suggested_tags as $tag) {
                    if (!isset($this->parsedData['tags'][$tag])) {
                        $this->parsedData['tags'][$tag] = 0;
                    }
                    $this->parsedData['tags'][$tag]++;
                }
            }

            // Count comments
            if ($feedback->comments) {
                $this->parsedData['commentCount'] += $feedback->comments->count();
            }
        }

        // Calculate repetition count based on similar content
        $contentHashes = [];
        foreach ($this->feedbackData as $feedback) {
            $contentHash = md5(strtolower(trim($feedback->body)));
            if (!isset($contentHashes[$contentHash])) {
                $contentHashes[$contentHash] = 0;
            }
            $contentHashes[$contentHash]++;
        }

        // Consider reports with 2+ similar content as repetitions
        foreach ($contentHashes as $hash => $count) {
            if ($count > 1) {
                $this->parsedData['repetitionCount'] += ($count - 1);
            }
        }

        // Calculate priority score (0-100)
        $this->parsedData['priorityScore'] = $this->calculatePriorityScore();

        // Determine recommended department
        $this->parsedData['recommendedDepartment'] = $this->determineRecommendedDepartment();
    }

    /**
     * Calculate a priority score for the feedback
     *
     * @return int Priority score (0-100)
     */
    protected function calculatePriorityScore(): int
    {
        if ($this->feedbackData->isEmpty()) {
            return 0;
        }

        $score = 0;

        // Volume factors (0-30 points)
        $uniqueReports = $this->parsedData['uniqueReports'];
        if ($uniqueReports >= 10) {
            $score += 30;
        } elseif ($uniqueReports >= 5) {
            $score += 20;
        } elseif ($uniqueReports >= 2) {
            $score += 10;
        } else {
            $score += 5;
        }

        // Urgency factors (0-30 points)
        $urgencyScore = 0;
        $urgencyLevels = $this->parsedData['urgencyLevels'];
        $urgencyTotal = array_sum($urgencyLevels);

        if ($urgencyTotal > 0) {
            $urgencyScore = (($urgencyLevels['critical'] * 30) +
                            ($urgencyLevels['high'] * 20) +
                            ($urgencyLevels['medium'] * 10) +
                            ($urgencyLevels['low'] * 5)) / $urgencyTotal;
        }
        $score += $urgencyScore;

        // Sentiment factor (0-20 points)
        $sentimentScore = 0;
        $sentimentCounts = $this->parsedData['sentimentCounts'];
        $sentimentTotal = array_sum($sentimentCounts);

        if ($sentimentTotal > 0) {
            $negativePercentage = ($sentimentCounts['negative'] / $sentimentTotal) * 100;
            $sentimentScore = min(20, $negativePercentage / 5);
        }
        $score += $sentimentScore;

        // Repetition factor (0-10 points)
        $repetitionScore = min(10, $this->parsedData['repetitionCount'] * 2);
        $score += $repetitionScore;

        // Engagement factor - comments (0-10 points)
        $commentScore = min(10, $this->parsedData['commentCount'] / 2);
        $score += $commentScore;

        return min(100, (int)$score);
    }

    /**
     * Determine the most appropriate department to handle the issues
     *
     * @return string Department name
     */
    protected function determineRecommendedDepartment(): string
    {
        $departmentSuggestions = $this->parsedData['departmentSuggestions'];

        if (empty($departmentSuggestions)) {
            // Default to department based on issue type
            $defaultDepartments = [
                'road' => 'Municipal Road Maintenance',
                'water' => 'Water Services Department',
                'electricity' => 'Power and Utilities',
                'waste' => 'Waste Management',
                'safety' => 'Public Safety Office',
                'education' => 'Education Department',
                'health' => 'Public Health Services'
            ];

            return $defaultDepartments[$this->issueType] ?? 'Municipal Services';
        }

        // Return the most frequently suggested department
        arsort($departmentSuggestions);
        return array_key_first($departmentSuggestions);
    }

    /**
     * Generate response using the feedback data and AI
     *
     * @param string $query The original user query
     * @return string The generated response
     */
    protected function generateResponse(string $query): string
    {
        // Return a prompt message if no data found
        if ($this->feedbackData->isEmpty()) {
            return "No feedback data found matching your query criteria. Please try with different parameters.";
        }

        // Prepare context for AI using processed data
        $context = $this->prepareContextForAI();

        // Send query and context to AI assistant
        $response = $this->ask($query, [
            'context' => $context
        ]);

        return $response;
    }

    /**
     * Generate streaming response using the feedback data and AI
     *
     * @param string $query The original user query
     * @return \Generator Streaming response
     */
    protected function generateResponseStream(string $query): \Generator
    {
        // Return a prompt message if no data found
        if ($this->feedbackData->isEmpty()) {
            yield "No feedback data found matching your query criteria. Please try with different parameters.";
            return;
        }

        // Prepare context for AI using processed data
        $context = $this->prepareContextForAI();

        // Send query and context to AI assistant using streaming
        foreach ($this->askStream($query, [
            'context' => $context
        ]) as $chunk) {
            yield $chunk;
        }
    }

    /**
     * Prepare context for the AI based on processed data
     *
     * @return string Context information
     */
    protected function prepareContextForAI(): string
    {
        $context = "Feedback Analysis Context:\n";
        $context .= "Location: " . ($this->location ?: "All locations") . "\n";
        $context .= "Issue Type: " . ($this->issueType ?: "All issues") . "\n";
        $context .= "Time Frame: " . ($this->timeFrame ?: "All time") . "\n";
        $context .= "Unique Reports: " . $this->parsedData['uniqueReports'] . "\n";
        $context .= "Repetitions: " . $this->parsedData['repetitionCount'] . "\n";

        if (!empty($this->parsedData['firstReport'])) {
            $context .= "First Report: " . $this->parsedData['firstReport']->format('Y-m-d') . "\n";
            $context .= "Latest Report: " . $this->parsedData['latestReport']->format('Y-m-d') . "\n";
        }

        $context .= "Sentiment Distribution: ";
        $context .= "Positive: " . $this->parsedData['sentimentCounts']['positive'] . ", ";
        $context .= "Negative: " . $this->parsedData['sentimentCounts']['negative'] . ", ";
        $context .= "Neutral: " . $this->parsedData['sentimentCounts']['neutral'] . "\n";

        $context .= "Urgency Levels: ";
        $context .= "Critical: " . $this->parsedData['urgencyLevels']['critical'] . ", ";
        $context .= "High: " . $this->parsedData['urgencyLevels']['high'] . ", ";
        $context .= "Medium: " . $this->parsedData['urgencyLevels']['medium'] . ", ";
        $context .= "Low: " . $this->parsedData['urgencyLevels']['low'] . "\n";

        // Include top tags
        if (!empty($this->parsedData['tags'])) {
            $tags = $this->parsedData['tags'];
            arsort($tags);
            $topTags = array_slice($tags, 0, 5, true);
            $context .= "Top Tags: " . implode(", ", array_keys($topTags)) . "\n";
        }

        $context .= "Priority Score: " . $this->parsedData['priorityScore'] . "/100\n";
        $context .= "Recommended Department: " . $this->parsedData['recommendedDepartment'] . "\n";

        // Include sample feedback content (limited to first 3)
        $context .= "\nSample Feedback Content:\n";
        foreach ($this->feedbackData->take(3) as $index => $feedback) {
            $context .= "Feedback #" . ($index + 1) . ":\n";
            $context .= "Title: " . $feedback->title . "\n";
            $context .= "Content: " . substr($feedback->body, 0, 150) . (strlen($feedback->body) > 150 ? "..." : "") . "\n";
            $context .= "-----------\n";
        }

        return $context;
    }

    public function instructions(): string
    {
        return new SystemPrompt(
            background: [
                'You are an AI assistant for a government feedback analysis system called PulseGov.',
                'Your purpose is to help officials analyze citizen feedback, identify trends, and provide actionable insights.',
                'You have access to a database of citizen feedback that includes metadata such as: feedback type, sentiment, urgency level, location, timestamps, categories, and department assignments.',
                'Citizens provide feedback which is analyzed and categorized by the system. Feedback can be complaints, suggestions, questions, or compliments.',
                'Feedback data also includes AI-enriched metadata like sentiment analysis (positive, negative, neutral), urgency levels (high, medium, low), and clustering by location and topics.',
                'The system logs user votes on feedback and tracks related comments.',
                'Your analysis should help government officials identify patterns, prioritize issues, and make data-driven decisions.',
            ],
            steps: [
                'Parse the user\'s request to understand the specific feedback data they\'re asking for.',
                'Retrieve relevant feedback data from the database based on filters like location (e.g., "Dardania"), issue type (e.g., "road issues"), timeframe, and urgency.',
                'Analyze the retrieved feedback data to identify patterns, trends, and actionable insights.',
                'Group similar feedback by location clusters, topic clusters, or categories.',
                'Calculate metrics such as feedback volume, sentiment distribution, and urgency levels.',
                'Extract key details such as first report date, number of unique reports, and repetitions of the same issue.',
                'Identify the most appropriate department for handling the issue based on the feedback content.',
                'Assign a priority score to the issue based on volume, urgency, impact, and sentiment.',
                'Generate location metadata using coordinates or district names when available.',
            ],
            output: [
                'Provide a clear, concise summary of the feedback analysis results.',
                'Include a count of unique citizen reports on the issue and highlight if it\'s a recurring problem.',
                'Describe the issue in sufficient detail, including its exact location and impact on citizens.',
                'Note the duration of the issue (e.g., "persisted for more than 2 weeks").',
                'Indicate the overall sentiment using language that reflects the severity and emotion.',
                'Include a specific recommendation with a timeframe for action.',
                'After the main summary, include an "AI-Extracted Metadata" section with:',
                '- Location Cluster: Geographic coordinates and radius',
                '- First Report: When the issue was first reported',
                '- Repetitions: Number of confirmed re-reports',
                '- Tags: Relevant keywords extracted from the feedback',
                '- Priority Score: Numerical score out of 100',
                '- Recommended Department: The most appropriate department to handle the issue',
            ]
        );
    }
}
