<?php

namespace TransformStudios\Front;

use Statamic\Facades\User;

class ChatConfig
{
    public static function forCurrentUser(): array
    {
        $email = User::current()?->email();

        return [
            'chatId' => config('front.chat.id'),
            'email' => $email,
            'hash' => $email ? hash_hmac('sha256', $email, config('front.secret_key')) : null,
            'name' => User::current()?->name(),
        ];
    }
}
