<?php

namespace TransformStudios\Front\Tests\Unit;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
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
        $user = User::make()
            ->email('erin@transformstudios.com')
            ->save();

        $notification = new TestNotification([
            'date' => Carbon::now()->toDateTimeString(),
            'name' => 'Erin Dalzell',
            'event' => 'alert_raised',
            'locations' => ['US Central'],
            'output' => 'HTTP WARNING: HTTP/1.1 403 Forbidden - 1220 bytes in 0.694 second response time',
            'subject' => 'Monitor Alert: Error Detected',
            'test' => 'Some Test',
            'type' => 'HTTP(S)',
        ]);

        $this->assertTrue((new Channel)->send($user, $notification));
    }

    /** @test */
    public function it_stores_the_conversation_id()
    {
        $user = User::make()
            ->email('erin@transformstudios.com')
            ->save();

        $notification = new TestNotification([
            'date' => Carbon::now()->toDateTimeString(),
            'email' => 'erin@transformstudios.com',
            'event' => 'alert_raised',
            'subject' => 'Monitor Alert: Error Detected',
            'test' => 'Some Test',
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

        $this->assertNull(User::findByEmail('erin@transformstudios.com')->get('conversation_id'));
        $this->assertTrue((new Channel)->send($user, $notification));
        $this->assertEquals('cnv_id', User::findByEmail('erin@transformstudios.com')->get('conversation_id'));
    }

    /** @test */
    public function it_removes_the_conversation_id_when_alert_cleared()
    {
        $user = User::make()
            ->email('erin@transformstudios.com')
            ->set('conversation_id', 'cnv_123')
            ->save();

        $notification = new TestNotification([
            'date' => Carbon::now()->toDateTimeString(),
            'email' => 'erin@transformstudios.com',
            'event' => 'alert_cleared',
            'is_up' => true,
            'subject' => 'Monitor Alert: Error Detected',
            'test' => 'Some Test',
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

        $this->assertNotNull(User::findByEmail('erin@transformstudios.com')->get('conversation_id'));
        $this->assertTrue((new Channel)->send($user, $notification));
        $this->assertNull(User::findByEmail('erin@transformstudios.com')->get('conversation_id'));
    }

    /** @test */
    public function it_adds_to_the_conversation_when_conversation_id_exists()
    {
        $user = User::make()
            ->email('erin@transformstudios.com')
            ->set('conversation_id', 'cnv_d9eeq8f')
            ->save();

        $notification = new TestNotification(
            [
                'date' => Carbon::now()->toDateTimeString(),
                'email' => 'erin@transformstudios.com',
                'event' => 'alert_raised',
                'subject' => 'Monitor Alert: Error Detected',
                'test' => 'Some Test',
            ]
        );

        $anotherNotification = new TestNotification(
            [
                'date' => Carbon::now()->toDateTimeString(),
                'email' => 'erin@transformstudios.com',
                'event' => 'alert_cleared',
                'subject' => 'Monitor Alert: Error Cleared',
                'test' => 'Some Test',
            ]
        );
        $channel = new Channel;

        $this->assertTrue($channel->send($user, $notification));

        $this->assertTrue($channel->send($user, $anotherNotification));
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
