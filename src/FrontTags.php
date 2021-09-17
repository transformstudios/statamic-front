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

        if (! $user = User::current()) {
            return view('front::head', [
                'chatId' => config('front.chat_id'),
            ])->render();
        }

        return view('front::head', [
            'chatId' => config('front.chat_id'),
            'email' => $user->email(),
            'hash' => hash_hmac('sha256', $user->email(), config('front.secret_key')),
            'name' => $user->get('name'),
        ])->render();
    }

    private function isConfigured(): bool
    {
        return config('front.show_on_front_end') &&
            config('front.chat_id') &&
            config('front.secret_key');
    }
}
