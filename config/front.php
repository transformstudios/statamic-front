<?php

return [
    'api_token' => env('FRONT_API_TOKEN'),
    'channel' => env('FRONT_CHANNEL'),
    'chat_id' => env('FRONT_CHAT_ID'),
    'secret_key' => env('FRONT_SECRET_KEY'),
    'show_on_control_panel' => env('FRONT_SHOW_ON_CONTROL_PANEL', true),
    'show_on_front_end' => env('FRONT_SHOW_ON_FRONT_END', false),
];
