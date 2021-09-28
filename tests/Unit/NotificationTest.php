<?php

namespace TransformStudios\Front\Tests\Unit;

use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Statamic\Facades\User;
use TransformStudios\Front\Notifications\Channel;
use TransformStudios\Front\Tests\TestCase;

class NotificationTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function can_send_front_message()
    {
        $user1 = User::make()
            ->email('erin@transformstudios.com')
            ->save();
        $user2 = User::make()
            ->email('erin@silentz.co')
            ->save();

        $users = collect([$user1, $user2]);

        $notification = new TestNotification([
            'date' => Carbon::now()->toDateTimeString(),
            'event' => 'alert_raised',
            'id' => '123',
            'locations' => ['US Central'],
            'output' => 'HTTP WARNING: HTTP/1.1 403 Forbidden - 1220 bytes in 0.694 second response time',
            'subject' => 'Monitor Alert: Error Detected',
            'test' => 'Some Test',
            'type' => 'HTTP(S)',
            'users' => $users,
        ]);

        $this->assertTrue((new Channel)->send(new AnonymousNotifiable, $notification));
    }

    /** @test */
    public function it_stores_the_conversation_id()
    {
        $user = User::make()
            ->email('erin@transformstudios.com')
            ->save();

        $notification = new TestNotification([
            'date' => Carbon::now()->toDateTimeString(),
            'event' => 'alert_raised',
            'id' => '123',
            'subject' => 'Monitor Alert: Error Detected',
            'test' => 'Some Test',
            'users' => collect([$user]),
        ]);

        Http::fake(function ($request) {
            return Http::response([
                '_links' => [
                    'self' => 'https://transform-studios.api.frontapp.com/messages/msg_id',
                    'related' => [
                        'conversation' => 'https://transform-studios.api.frontapp.com/conversations/cnv_id',
                    ],
                ],
            ], 200);
        });

        $this->assertNull(Cache::get('123'));
        $this->assertTrue((new Channel)->send(new AnonymousNotifiable, $notification));
        $this->assertEquals('cnv_id', Cache::get('123'));
    }

    /** @test */
    public function it_removes_the_conversation_id_when_alert_cleared()
    {
        $user = User::make()
            ->email('erin@transformstudios.com')
            ->save();

        $notification = new TestNotification([
            'date' => Carbon::now()->toDateTimeString(),
            'email' => 'erin@transformstudios.com',
            'event' => 'alert_cleared',
            'id' => '123',
            'is_up' => true,
            'subject' => 'Monitor Alert: Error Detected',
            'test' => 'Some Test',
            'users' => collect([$user]),
        ]);

        Http::fake(function ($request) {
            return Http::response([
                '_links' => [
                    'self' => 'https://transform-studios.api.frontapp.com/messages/msg_id',
                    'related' => [
                        'conversation' => 'https://transform-studios.api.frontapp.com/conversations/cnv_id',
                    ],
                ],
            ], 200);
        });

        Cache::put('123', 'cnv_123');
        $this->assertNotNull(Cache::get('123'));
        $this->assertTrue((new Channel)->send(new AnonymousNotifiable, $notification));
        $this->assertNull(Cache::get('123'));
    }

    /** @test */
    public function it_adds_to_the_conversation_when_conversation_id_exists()
    {
        $user = User::make()
            ->email('erin@transformstudios.com')
            ->save();

        $notification = new TestNotification(
            [
                'date' => Carbon::now()->toDateTimeString(),
                'event' => 'alert_raised',
                'id' => '123',
                'subject' => 'Monitor Alert: Error Detected',
                'test' => 'Some Test',
                'users' => collect([$user]),
            ]
        );

        $anotherNotification = new TestNotification(
            [
                'date' => Carbon::now()->toDateTimeString(),
                'event' => 'alert_cleared',
                'id' => '123',
                'subject' => 'Monitor Alert: Error Cleared',
                'test' => 'Some Test',
                'users' => collect([$user]),
            ]
        );
        $channel = new Channel;

        $this->assertTrue($channel->send(new AnonymousNotifiable, $notification));

        $this->assertTrue($channel->send(new AnonymousNotifiable, $anotherNotification));
    }
}

class TestNotification extends Notification
{
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
