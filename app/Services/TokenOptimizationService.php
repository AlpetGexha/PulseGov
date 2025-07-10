<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\Log;

final class TokenOptimizationService
{
    private const MAX_CONVERSATION_TOKENS = 6000;
    private const PRIORITY_MESSAGES = 5; // Always keep last 5 messages

    public function optimizeConversationHistory(Conversation $conversation): array
    {
        $messages = $conversation->messages()
            ->orderBy('created_at', 'desc')
            ->get();

        if ($messages->isEmpty()) {
            return [];
        }

        // Always include the most recent messages
        $priorityMessages = $messages->take(self::PRIORITY_MESSAGES);
        $remainingMessages = $messages->skip(self::PRIORITY_MESSAGES);

        $totalTokens = $priorityMessages->sum('token_count');
        $optimizedMessages = $priorityMessages->reverse()->toArray();

        // Add older messages if we have token budget
        foreach ($remainingMessages as $message) {
            if ($totalTokens + $message->token_count > self::MAX_CONVERSATION_TOKENS) {
                break;
            }

            $totalTokens += $message->token_count;
            array_unshift($optimizedMessages, $message->toArray());
        }

        Log::info('Optimized conversation history', [
            'conversation_id' => $conversation->id,
            'original_messages' => $messages->count(),
            'optimized_messages' => count($optimizedMessages),
            'total_tokens' => $totalTokens,
        ]);

        return $optimizedMessages;
    }

    public function estimateTokens(string $text): int
    {
        // More accurate token estimation
        // GPT-4 typically uses ~0.75 tokens per word
        $wordCount = str_word_count($text);
        $charCount = mb_strlen($text);

        // Estimate based on both words and characters
        $wordBasedTokens = $wordCount * 0.75;
        $charBasedTokens = $charCount / 4;

        // Use the higher estimate to be safe
        return (int) (max($wordBasedTokens, $charBasedTokens));
    }

    public function shouldCompressHistory(Conversation $conversation): bool
    {
        return $conversation->token_usage > self::MAX_CONVERSATION_TOKENS;
    }

    public function compressOldMessages(Conversation $conversation): void
    {
        $oldMessages = $conversation->messages()
            ->where('created_at', '<', now()->subDays(7))
            ->orderBy('created_at', 'asc')
            ->get();

        $compressedContent = $this->createSummary($oldMessages);

        if (! empty($compressedContent)) {
            // Create a summary message
            Message::create([
                'conversation_id' => $conversation->id,
                'role' => 'system',
                'content' => 'Previous conversation summary: ' . $compressedContent,
                'token_count' => $this->estimateTokens($compressedContent),
                'metadata' => ['type' => 'summary', 'original_messages' => $oldMessages->count()],
            ]);

            // Delete old messages
            $oldMessages->each->delete();

            Log::info('Compressed conversation history', [
                'conversation_id' => $conversation->id,
                'compressed_messages' => $oldMessages->count(),
                'summary_tokens' => $this->estimateTokens($compressedContent),
            ]);
        }
    }

    private function createSummary($messages): string
    {
        if ($messages->isEmpty()) {
            return '';
        }

        $userMessages = $messages->where('role', 'user')->pluck('content');
        $assistantMessages = $messages->where('role', 'assistant')->pluck('content');

        $summary = 'User discussed: ' . $userMessages->take(3)->implode('; ') .
                  '. Assistant provided information about: ' .
                  $assistantMessages->take(3)->implode('; ');

        return mb_substr($summary, 0, 500) . '...';
    }
}
