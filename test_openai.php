<?php

require_once 'vendor/autoload.php';

use OpenAI;

try {
    $client = OpenAI::client('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');

    $response = $client->chat()->create([
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role' => 'user', 'content' => 'Hello, can you respond?']
        ],
        'max_tokens' => 50
    ]);

    echo "OpenAI Test Success: " . $response->choices[0]->message->content . "\n";
} catch (Exception $e) {
    echo "OpenAI Error: " . $e->getMessage() . "\n";
}
