<?php

return [
    'secret_key' => env('FRONT_SECRET_KEY'),
    'chat' => [
        'id' => env('FRONT_CHAT_ID'),
        'show_on_control_panel' => env('FRONT_SHOW_ON_CONTROL_PANEL', true),
        'show_on_front_end' => env('FRONT_SHOW_ON_FRONT_END', false),
    ],
];
