<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | AI Chat Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for the PulseGov AI Chat system
    |
    */

    'openai' => [
        'model' => env('CHAT_OPENAI_MODEL', 'gpt-3.5-turbo'),
        'max_tokens' => (int) env('CHAT_MAX_TOKENS', 4000),
        'temperature' => (float) env('CHAT_TEMPERATURE', 0.7),
        'timeout' => (int) env('CHAT_TIMEOUT', 120), // seconds
    ],

    'conversation' => [
        'max_context_tokens' => (int) env('CHAT_MAX_CONTEXT_TOKENS', 8000),
        'token_warning_threshold' => (int) env('CHAT_TOKEN_WARNING', 6000),
        'auto_compress_after_days' => (int) env('CHAT_AUTO_COMPRESS_DAYS', 7),
        'max_conversations_per_user' => (int) env('CHAT_MAX_CONVERSATIONS', 50),
    ],

    'feedback' => [
        'sample_size' => (int) env('CHAT_FEEDBACK_SAMPLE_SIZE', 50),
        'cache_duration' => (int) env('CHAT_FEEDBACK_CACHE_DURATION', 3600), // seconds
        'include_ai_analysis' => (bool) env('CHAT_INCLUDE_AI_ANALYSIS', true),
    ],

    'rate_limiting' => [
        'messages_per_minute' => (int) env('CHAT_RATE_LIMIT_MESSAGES', 10),
        'tokens_per_hour' => (int) env('CHAT_RATE_LIMIT_TOKENS', 50000),
    ],

    'system_prompts' => [
        'base' => 'You are PulseGov AI, an intelligent assistant for government officials analyzing citizen feedback.',
        'context_template' => 'Current Feedback Database Summary: {summary}',
        'error_message' => 'I apologize, but I\'m experiencing technical difficulties. Please try again in a moment.',
    ],
];
