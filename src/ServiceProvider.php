<?php

namespace TransformStudios\Front;

use Illuminate\Support\Facades\View;
use Statamic\Facades\User;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    protected $scripts = [
        __DIR__.'/../dist/js/cp.js',
    ];

    protected $stylesheets = [
        __DIR__.'/../resources/css/cp.css',
    ];

    public function boot()
    {
        parent::boot();

        View::composer('statamic::layout', function ($view) {
            if (! $user = User::current()) {
                return;
            }

            Statamic::provideToScript(
                [
                    'front' => [
                        'chatId' => config('front.chat_id'),
                        'email' => $user->email(),
                        'hash' => hash_hmac('sha256', $user->email(), config('front.secret_key')),
                    ],
                ]
            );
        });
    }
}
