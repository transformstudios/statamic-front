<?php

namespace TransformStudios\Front\Logging;

use Error;
use Illuminate\Support\Facades\Http;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger as Monolog;

class LogHandler extends AbstractProcessingHandler
{
    /** @throws \Illuminate\Contracts\Container\BindingResolutionException */
    public function __construct(array $channelConfig)
    {
        parent::__construct(Monolog::toMonologLevel($channelConfig['level'] ?? Monolog::DEBUG));
    }

    public function write(array $record): void
    {
        $message = $record['message'];

        /** @var Error */
        $error = $record['context']['exception'];

        $body = '**'.$error->getMessage().'**'.PHP_EOL;
        $body .= '* '.$error->getFile().' ('.$error->getLine().')'.PHP_EOL;

        $body .= collect($error->getTrace())
            ->take(10)
            ->map(function (array $traceItem) {
                return '* '.$traceItem['file'].' ('.$traceItem['line'].')';
            })->implode(PHP_EOL);

        $client = Http::withToken(config('front.api_token'))->baseUrl(
            'https://api2.frontapp.com'
        );
        $conversation = 'cnv_enblj7z';

        $client
            ->post("/conversations/$conversation/comments", ['body' => $body])
            ->throw();
    }
}
