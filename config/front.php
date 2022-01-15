<?php

return [
    'api_token' => env('FRONT_API_TOKEN'),
    'secret_key' => env('FRONT_SECRET_KEY'),
    'chat' => [
        'id' => env('FRONT_CHAT_ID'),
        'show_on_control_panel' => env('FRONT_SHOW_ON_CONTROL_PANEL', true),
        'show_on_front_end' => env('FRONT_SHOW_ON_FRONT_END', false),
    ],
    'logging' => [
        'conversation_id' => env('FRONT_CONVERSATION_ID'),
    ],
    'notifications' => [
        'channel' => env('FRONT_CHANNEL'),
    ],
];
