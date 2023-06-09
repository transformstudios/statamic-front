<?php

namespace TransformStudios\Front\Logging;

use Illuminate\Support\Collection;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger as Monolog;
use Monolog\LogRecord;
use Statamic\Support\Arr;
use Throwable;

class LogHandler extends AbstractProcessingHandler
{
    /** @throws \Illuminate\Contracts\Container\BindingResolutionException */
    public function __construct(array $channelConfig)
    {
        parent::__construct(Monolog::toMonologLevel($channelConfig['level'] ?? Monolog::DEBUG));
    }

    public function write(LogRecord $record): void
    {
        if (! $conversation = config('front.logging.conversation_id')) {
            return;
        }

        if (! Arr::get($record->context, 'exception')) {
            $errors = collect(
                [
                    'Request URL: '.request()->fullUrl(),
                    'Request data: '.json_encode(request()->input()),
                    'Error: '.json_encode($record->toArray()),
                ]
            );

            front()
                ->post(
                    "/conversations/$conversation/comments",
                    ['body' => $errors->implode(PHP_EOL)]
                )->throw();

            return;
        }

        front()
            ->post(
                "/conversations/$conversation/comments",
                $this->convertErrorToFrontMessage(Arr::get($record->context, 'exception'))
            )->throw();
    }

    private function convertErrorToFrontMessage(Throwable $error): array
    {
        return ['body' => $this->formatErrorLines($error)->implode(PHP_EOL)];
    }

    private function formatErrorLines(Throwable $error): Collection
    {
        return collect(
            [
                'Request URL: '.request()->fullUrl(),
                'Request data: '.json_encode(request()->input()),
                '**'.$error->getMessage().'**',
                '* '.$error->getFile().' ('.$error->getLine().')',
            ]
        )->merge($this->formatStackTrace($error));
    }

    private function formatStackTrace(Throwable $error): Collection
    {
        return collect($error->getTrace())
            ->take(10)
            ->map(function (array $traceItem) {
                if (! $file = Arr::get($traceItem, 'file')) {
                    return '* '.json_encode($traceItem);
                }

                return '* '.$file.' ('.Arr::get($traceItem, 'line').')';
            });
    }
}
