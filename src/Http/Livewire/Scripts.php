<?php

namespace TransformStudios\Front\Http\Livewire;

use Livewire\Component;
use Statamic\Facades\User;

class Scripts extends Component
{
    public function render()
    {
        $u = [];

        if ($user = User::current()) {
            $u['email'] = $user->email();
            $u['hash'] = hash_hmac('sha256', $u['email'], config('front.secret_key'));
            $u['name'] = $user->get('name');
        }

        return view('front::head', [
            'configured' => $this->isConfigured(),
            'chatId' => config('front.chat.id'),
            'user' => $u,
        ]);
    }

    private function isConfigured(): bool
    {
        return config('front.chat.show_on_front_end') &&
            config('front.chat.id') &&
            config('front.secret_key');
    }
}
