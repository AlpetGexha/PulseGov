<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ProcessChatMessage;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\TokenOptimizationService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ChatController extends Controller
{
    /**
     * Display the chat interface.
     */
    public function index()
    {
        $conversations = Conversation::forUser(Auth::id())
            ->active()
            ->with('lastMessage')
            ->orderBy('last_activity_at', 'desc')
            ->get();

        return Inertia::render('Chat/Index', [
            'conversations' => $conversations,
        ]);
    }

    /**
     * Create a new conversation.
     */
    public function createConversation(Request $request)
    {
        $conversation = Conversation::create([
            'user_id' => Auth::id(),
            'title' => 'New Conversation ' . now()->format('M j, Y g:i A'),
            'is_active' => true,
            'last_activity_at' => now(),
        ]);

        return response()->json([
            'conversation' => $conversation->load('messages'),
        ]);
    }

    /**
     * Get a specific conversation with messages.
     */
    public function getConversation(Conversation $conversation)
    {
        // Ensure user owns this conversation
        if ($conversation->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $conversation->load('messages');

        return response()->json([
            'conversation' => $conversation,
        ]);
    }

    /**
     * Send a message in a conversation.
     */
    public function sendMessage(Request $request, Conversation $conversation, TokenOptimizationService $tokenService)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        // Ensure user owns this conversation
        if ($conversation->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            // Process the message using the action
            $processChatMessage = new ProcessChatMessage($tokenService);
            $result = $processChatMessage->handle($conversation, $request->message);

            // Update conversation activity
            $conversation->updateActivity();

            return response()->json([
                'success' => true,
                'user_message' => $result['user_message'],
                'assistant_message' => $result['assistant_message'],
                'token_usage' => $result['token_usage'],
            ]);

        } catch (Exception $e) {
            Log::error('Chat message error', [
                'error' => $e->getMessage(),
                'conversation_id' => $conversation->id,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'error' => 'Failed to process message. Please try again.',
            ], 500);
        }
    }

    /**
     * Delete a conversation.
     */
    public function deleteConversation(Conversation $conversation)
    {
        // Ensure user owns this conversation
        if ($conversation->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $conversation->update(['is_active' => false]);

        return response()->json(['success' => true]);
    }

    /**
     * Get conversation history for sidebar.
     */
    public function getConversationHistory()
    {
        $conversations = Conversation::forUser(Auth::id())
            ->active()
            ->with('lastMessage')
            ->orderBy('last_activity_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'conversations' => $conversations,
        ]);
    }
}
