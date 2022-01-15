<?php

namespace TransformStudios\Front\Logging;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger as Monolog;
use Statamic\Support\Arr;
use Throwable;

class LogHandler extends AbstractProcessingHandler
{
    /** @throws \Illuminate\Contracts\Container\BindingResolutionException */
    public function __construct(array $channelConfig)
    {
        parent::__construct(Monolog::toMonologLevel($channelConfig['level'] ?? Monolog::DEBUG));
    }

    public function write(array $record): void
    {
        $conversation = config('front.logging.conversation_id');

        $data = $this->convertErrorToFrontMessage(Arr::get($record, 'context.exception'));
        front()
            ->post("/conversations/$conversation/comments", $data)
            ->throw();
    }

    private function convertErrorToFrontMessage(Throwable $error): array
    {
        $body = '**'.$error->getMessage().'**'.PHP_EOL;
        $body .= '* '.$error->getFile().' ('.$error->getLine().')'.PHP_EOL;

        $body .= collect($error->getTrace())
            ->take(10)
            ->map(fn (array $traceItem) => '* '.$traceItem['file'].' ('.$traceItem['line'].')')
            ->implode(PHP_EOL);

        return ['body' => $body];
    }
}
