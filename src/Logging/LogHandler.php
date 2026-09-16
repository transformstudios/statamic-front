<?php

namespace TransformStudios\Front\Logging;

use Illuminate\Support\Collection;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\Logger as Monolog;
use Monolog\LogRecord;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Throwable;

class LogHandler extends AbstractProcessingHandler
{
    /** @throws \Illuminate\Contracts\Container\BindingResolutionException */
    public function __construct(array $channelConfig)
    {
        parent::__construct(Monolog::toMonologLevel($channelConfig['level'] ?? Level::Debug));
    }

    public function write(array|LogRecord $record): void
    {
        if (! $conversation = config('front.logging.conversation_id')) {
            return;
        }

        if ($record instanceof LogRecord) {
            $record = $record->toArray();
        }

        if (! $error = Arr::get($record, 'context.exception')) {
            $errors = collect([
                'Request URL: '.request()->fullUrl(),
                'Request data: '.json_encode(request()->input()),
                'Error: '.json_encode($record),
            ]);

            front()
                ->post(
                    "/conversations/$conversation/comments",
                    ['body' => Str::limit($errors->implode(PHP_EOL), 10000)]
                )->throw();

            return;
        }

        front()
            ->post(
                "/conversations/$conversation/comments",
                $this->convertErrorToFrontMessage($error, Arr::except(Arr::get($record, 'context', []), 'exception'))
            )->throw();
    }

    private function convertErrorToFrontMessage(Throwable $error, array $context): array
    {
        return ['body' => $this->formatErrorLines($error, $context)->implode(PHP_EOL)];
    }

    private function formatContext(array $context): Collection
    {
        return collect($context)
            ->map(fn ($value, string $key) => '* '.$key.': '.(is_string($value) ? $value : json_encode($value)))
            ->values();
    }

    private function formatErrorLines(Throwable $error, array $context): Collection
    {
        return collect([
            'Request URL: '.request()->fullUrl(),
            'Request data: '.json_encode(request()->input()),
            '**'.$error->getMessage().'**',
            '* '.$error->getFile().' ('.$error->getLine().')',
        ])
            ->merge($this->formatContext($context))
            ->merge($this->formatStackTrace($error));
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
