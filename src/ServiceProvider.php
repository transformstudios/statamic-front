<?php

namespace TransformStudios\Front;

use Statamic\Facades\User;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

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
    ];

    public function bootAddon()
    {
        if (! config('front.chat.show_on_control_panel')) {
            return;
        }

        Statamic::provideToScript([
            'front' => fn () => User::current() ? ChatConfig::forCurrentUser() : null,
        ]);
    }
}
