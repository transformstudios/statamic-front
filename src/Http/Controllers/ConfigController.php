<?php

namespace TransformStudios\Front\Http\Controllers;

use Illuminate\Http\JsonResponse;
use TransformStudios\Front\ChatConfig;

class ConfigController
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'configured' => $this->isConfigured(),
            ...ChatConfig::forCurrentUser(),
        ]);
    }

    private function isConfigured(): bool
    {
        return config('front.chat.show_on_front_end') &&
            config('front.chat.id') &&
            config('front.secret_key');
    }
}
