<?php

namespace TransformStudios\Front;

use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\View;
use Statamic\Facades\User as UserFacade;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;
use Statamic\Support\Arr;
use TransformStudios\Front\Notifications\Channel;

class ServiceProvider extends AddonServiceProvider
{
    protected $routes = [
        'actions' => __DIR__.'/../routes/actions.php',
    ];

    protected $vite = [
        'input' => [
            'resources/css/cp.css',
            'resources/js/cp.js',
        ],
        'publicDirectory' => 'dist',
        'hotFile' => __DIR__.'/../dist/hot',
    ];

    public function bootAddon()
    {
        $this->bootScript();

        // needed for testing but not production
        // $this->loadViewsFrom(
        //     __DIR__.'/../resources/views',
        //     'front'
        // );
    }

    public function register()
    {
        Notification::resolved(
            fn (ChannelManager $service) => $service->extend('front', fn ($app) => new Channel)
        );
    }

    private function bootScript()
    {
        if (! config('front.chat.show_on_control_panel')) {
            return;
        }

        View::composer('statamic::layout', function ($view) {
            if (! $user = UserFacade::current()) {
                return;
            }

            Statamic::provideToScript(
                [
                    'front' => Arr::removeNullValues([
                        'chatId' => config('front.chat.id'),
                        'email' => $user->email(),
                        'hash' => hash_hmac('sha256', $user->email(), config('front.secret_key')),
                        'name' => $user->name ?? $user->first_name.' '.$user->last_name,
                    ]),
                ]
            );
        });
    }
}
