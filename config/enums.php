<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Enum Configurations
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for enum classes used throughout
    | the application. You can customize labels, colors, and other display
    | properties for different enum values.
    |
    */

    'feedback_status' => [
        'colors' => [
            'under_review' => 'yellow',
            'resolved' => 'blue',
            'implemented' => 'green',
        ],
        'icons' => [
            'under_review' => 'clock',
            'resolved' => 'check-circle',
            'implemented' => 'rocket-launch',
        ],
    ],

    'feedback_type' => [
        'colors' => [
            'suggestion' => 'blue',
            'problem' => 'red',
            'praise' => 'green',
        ],
        'icons' => [
            'suggestion' => 'light-bulb',
            'problem' => 'exclamation-triangle',
            'praise' => 'thumb-up',
        ],
    ],

    'feedback_sentiment' => [
        'colors' => [
            'positive' => 'green',
            'negative' => 'red',
            'neutral' => 'gray',
        ],
        'icons' => [
            'positive' => 'thumb-up',
            'negative' => 'thumb-down',
            'neutral' => 'minus',
        ],
    ],

    'urgency_level' => [
        'colors' => [
            'high' => 'red',
            'medium' => 'yellow',
            'low' => 'green',
        ],
        'icons' => [
            'high' => 'exclamation-circle',
            'medium' => 'exclamation',
            'low' => 'information-circle',
        ],
        'priority' => [
            'high' => 1,
            'medium' => 2,
            'low' => 3,
        ],
    ],

    'vote_type' => [
        'colors' => [
            'upvote' => 'green',
            'downvote' => 'red',
        ],
        'icons' => [
            'upvote' => 'thumb-up',
            'downvote' => 'thumb-down',
        ],
        'values' => [
            'upvote' => 1,
            'downvote' => -1,
        ],
    ],

    'user_role' => [
        'colors' => [
            'admin' => 'purple',
            'citizen' => 'blue',
        ],
        'icons' => [
            'admin' => 'shield-check',
            'citizen' => 'user',
        ],
    ],
];
