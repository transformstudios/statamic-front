<?php

namespace TransformStudios\Front\Tests\Unit;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
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

        $notification = new class() extends Notification {
            public function toArray()
            {
                return [
                    'email' => 'erin@transformstudios.com',
                    'test' => 'Some Test',
                    'date' => Carbon::now()->toDateTimeString(),
                    'event' => 'alert_raised',
                ];
            }
        };

        $this->assertTrue((new Channel)->send($user, $notification));
    }
}
