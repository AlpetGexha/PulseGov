<?php

namespace App\Console\Commands;

use App\Agents\FeedbackAgentChatBot;
use Illuminate\Console\Command;

class StreamFeedbackAgentChat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'agent:feedback-chat {query : The question or query to ask the feedback agent}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Stream chat responses from the FeedbackAgentChatBot';

    /**
     * Execute the console command.
     */
    public function handle(FeedbackAgentChatBot $agent)
    {
        $query = $this->argument('query');

        $this->info("Processing query: {$query}");
        $this->newLine();

        try {
            // Create a callback for streaming output
            $output = function ($chunk) {
                $this->output->write($chunk);
            };

            // Call the agent with streaming
            $response = $agent->processQueryStream($query);

            // The StreamedResponse already handles the streaming in the FeedbackAgentChatBot
            $this->newLine(2);
            $this->info('Query processing completed successfully.');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error processing query: {$e->getMessage()}");
            $this->newLine();
            $this->line($e->getTraceAsString());

            return self::FAILURE;
        }
    }
}
