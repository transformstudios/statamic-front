<?php

namespace TransformStudios\Front\Logging;

use Illuminate\Support\Collection;
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

        front()
            ->post(
                "/conversations/$conversation/comments",
                $this->convertErrorToFrontMessage(Arr::get($record, 'context.exception'))
            )->throw();
    }

    private function convertErrorToFrontMessage(Throwable $error): array
    {
        return ['body' => $this->formatErrorLines($error)->implode(PHP_EOL)];
    }

    private function formatErrorLines(Throwable $error): Collection
    {
        return collect([
                '**'.$error->getMessage().'**',
                '* '.$error->getFile().' ('.$error->getLine().')',
            ])->merge($this->formatStackTrace($error));
    }

    private function formatStackTrace(Throwable $error): Collection
    {
        return collect($error->getTrace())
            ->take(10)
            ->map(fn (array $traceItem) => '* '.$traceItem['file'].' ('.$traceItem['line'].')');
    }
}
