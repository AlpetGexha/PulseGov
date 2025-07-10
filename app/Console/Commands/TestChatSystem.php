<?php

namespace App\Console\Commands;

use App\Models\Conversation;
use App\Models\User;
use App\Actions\ProcessChatMessage;
use App\Services\TokenOptimizationService;
use Illuminate\Console\Command;

class TestChatSystem extends Command
{
    protected $signature = 'chat:test';
    protected $description = 'Test the chat system functionality';

    public function handle()
    {
        $this->info('Testing PulseGov Chat System...');

        try {
            // Find or create a test user
            $user = User::first();
            if (!$user) {
                $this->error('No users found. Please create a user first.');
                return;
            }

            // Create a test conversation
            $conversation = Conversation::create([
                'user_id' => $user->id,
                'title' => 'Test Conversation',
                'is_active' => true,
                'last_activity_at' => now()
            ]);

            $this->info("Created conversation ID: {$conversation->id}");

            // Test the chat processing
            $tokenService = new TokenOptimizationService();
            $processChatMessage = new ProcessChatMessage($tokenService);

            $testMessage = "Show me the latest feedback from citizens about road issues.";
            $this->info("Testing message: {$testMessage}");

            $result = $processChatMessage->handle($conversation, $testMessage);

            $this->info("✅ Chat system test completed successfully!");
            $this->info("User message ID: {$result['user_message']->id}");
            $this->info("Assistant message ID: {$result['assistant_message']->id}");
            $this->info("Total tokens used: {$result['token_usage']}");
            $this->info("Assistant response: " . substr($result['assistant_message']->content, 0, 100) . "...");

        } catch (\Exception $e) {
            $this->error("❌ Test failed: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
        }
    }
}
