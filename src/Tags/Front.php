<?php

namespace TransformStudios\Front\Tags;

use Illuminate\Support\Js;
use Statamic\Support\Arr;
use Statamic\Tags\Tags;
use TransformStudios\Front\ChatConfig;

class Front extends Tags
{
    public function chat(): string
    {
        if (! $this->isConfigured()) {
            return '';
        }

        $options = Js::from(Arr::removeNullValues(ChatConfig::forCurrentUser()));

        return <<<HTML
        <script src="https://chat-assets.frontapp.com/v1/chat.bundle.js"></script>
        <script>
            window.addEventListener('load', () => window.FrontChat('init', { ...{$options}, useDefaultLauncher: true }));
        </script>
        HTML;
    }

    private function isConfigured(): bool
    {
        return config('front.chat.show_on_front_end')
            && config('front.chat.id')
            && config('front.secret_key');
    }
}
