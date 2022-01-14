<?php

namespace TransformStudios\Front\Logging;

use Monolog\Logger as Monolog;

class Logger
{
    /** @throws \Illuminate\Contracts\Container\BindingResolutionException */
    public function __invoke(array $config)
    {
        return new Monolog(
            config('app.name'),
            [new LogHandler($config)]
        );
    }
}
