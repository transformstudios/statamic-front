<?php

namespace TransformStudios\Front;

use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\View;
use Statamic\Facades\User;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;
use TransformStudios\Front\Notifications\Channel;

class ServiceProvider extends AddonServiceProvider
{
    protected $scripts = [
        __DIR__.'/../dist/js/cp.js',
    ];

    protected $stylesheets = [
        __DIR__.'/../resources/css/cp.css',
    ];

    protected $tags = [
        FrontTags::class,
    ];

    public function boot()
    {
        parent::boot();

        $this->bootScript();
    }

    public function register()
    {
        Notification::resolved(
            fn (ChannelManager $service) => $service->extend('front', fn ($app) => new Channel)
        );
    }

    private function bootScript()
    {
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
