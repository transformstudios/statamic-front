<?php

use Illuminate\Support\Facades\Http;
use Monolog\Level;
use Monolog\LogRecord;
use TransformStudios\Front\Logging\LogHandler;

beforeEach(function () {
    config()->set('front.logging.conversation_id', 'cnv_id');
    Http::preventStrayRequests();
    Http::fake(['https://api2.frontapp.com/conversations/cnv_id/comments' => Http::response([], 200)]);
});

it('adds the log context to the comment', function () {
    (new LogHandler(['level' => 'error']))->write(record(context: [
        'exception' => new Exception('boom'),
        'redis' => ['loading' => 1, 'uptime_in_seconds' => 12],
        'userId' => 7,
    ]));

    Http::assertSent(function ($request) {
        expect($request['body'])
            ->toContain('* redis: {"loading":1,"uptime_in_seconds":12}')
            ->toContain('* userId: 7');

        return true;
    });
});

it('leaves the exception out of the rendered context', function () {
    (new LogHandler(['level' => 'error']))->write(record(context: ['exception' => new Exception('boom')]));

    Http::assertSent(function ($request) {
        expect($request['body'])
            ->toContain('**boom**')
            ->not->toContain('* exception:');

        return true;
    });
});

function record(array $context): LogRecord
{
    return new LogRecord(
        datetime: new DateTimeImmutable,
        channel: 'testing',
        level: Level::Error,
        message: 'boom',
        context: $context,
    );
}
