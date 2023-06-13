<?php

namespace TransformStudios\Front\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Statamic\Facades\User;

class ConfigController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $email = null;
        $hash = null;
        $name = null;

        if ($user = User::current()) {
            $email = $user->email();
            $hash = hash_hmac('sha256', $email, config('front.secret_key'));
            $name = $user->get('name');
        }

        return response()->json([
            'configured' => $this->isConfigured(),
            'chatId' => config('front.chat.id'),
            'email' => $email,
            'name' => $name,
            'hash' => $hash,
        ]);
    }

    private function isConfigured(): bool
    {
        return config('front.chat.show_on_front_end') &&
            config('front.chat.id') &&
            config('front.secret_key');
    }
}
