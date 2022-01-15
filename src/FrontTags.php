<?php

namespace TransformStudios\Front;

use Statamic\Facades\User;
use Statamic\Tags\Tags;

class FrontTags extends Tags
{
    protected static $handle = 'front';

    /**
     * The {{ front:head }} tag.
     *
     * @return string
     */
    public function scripts()
    {
        if (! $this->isConfigured()) {
            return;
        }

        if ($user = User::current()) {
            $email = $user->email();
            $hash = hash_hmac('sha256', $email, config('front.secret_key'));
            $name = $user->get('name');
        }

        return view('front::head', [
            'chatId' => config('front.chat.id'),
            'email' => $email ?? null,
            'hash' => $hash ?? null,
            'name' => $name ?? null,
        ])->render();
    }

    private function isConfigured(): bool
    {
        return config('front.chat.show_on_front_end') &&
            config('front.chat.id') &&
            config('front.secret_key');
    }
}
