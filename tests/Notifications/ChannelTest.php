<?php

use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Statamic\Facades\User;
use TransformStudios\Front\Notifications\BaseNotification;
use TransformStudios\Front\Notifications\Channel;

beforeEach(function () {
    config()->set('front.notifications.channel', 'test-channel');
    Http::preventStrayRequests();
    Http::fake([
        'https://api2.frontapp.com/channels/test-channel/messages' => Http::response([
            '_links' => [
                'related' => [
                    'conversation' => 'https://transform-studios.api.frontapp.com/conversations/cnv_id',
                ],
            ],
        ], 200),
        'https://api2.frontapp.com/conversations/cnv_id/messages' => Http::response([], 200),
    ]);
});

test('can send front message', function () {
    $users = collect([makeUser('erin@transformstudios.com'), makeUser('erin@silentz.co')]);
    $notification = new TestNotification('some-key', 'Monitor Alert: Error Detected', '', $users);

    expect((new Channel)->send(new AnonymousNotifiable, $notification))->toBeTrue();
});

it('stores the conversation id', function () {
    $notification = new TestNotification('some-key', 'Monitor Alert: Error Detected', '', collect([makeUser('foo@bar.com')]));

    expect(Cache::get('some-key'))->toBeNull();
    expect((new Channel)->send(new AnonymousNotifiable, $notification))->toBeTrue();
    expect(Cache::get('some-key'))->toEqual('cnv_id');
});

it('removes the conversation id when alert cleared', function () {
    $notification = new TestNotification('some-key', 'Monitor Alert: Error Cleared', '', collect([makeUser('foo@bar.com')]));

    Cache::put('some-key', 'cnv_id');
    expect(Cache::get('some-key'))->not->toBeNull();
    expect((new Channel)->send(new AnonymousNotifiable, $notification))->toBeTrue();
    expect(Cache::get('some-key'))->toBeNull();
});

it('adds to the conversation when conversation id exists', function () {
    $user = makeUser('foo@bar.com');
    $notification = new TestNotification('some-key', 'Monitor Alert: Error Detected', '', collect([$user]));
    $anotherNotification = new TestNotification('some-key', 'Monitor Alert: Error Cleared', '', collect([$user]));
    $channel = new Channel;

    expect($channel->send(new AnonymousNotifiable, $notification))->toBeTrue();
    expect($channel->send(new AnonymousNotifiable, $anotherNotification))->toBeTrue();
});

class TestNotification extends BaseNotification {}
