<?php

namespace TransformStudios\Front\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class Channel
{
    /**
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function send($notifiable, Notification $notification)
    {
        $data = $notification->toArray($notifiable);

        $channel = config('front.channel');

        $response = Http::withToken(config('front.api_token'))
            ->post(
                "https://api2.frontapp.com/channels/$channel/messages",
                [
                    'subject' => $data['subject'],
                    'body' => view("front::notifications.{$data['event']}", $data)->render(),
                    'to' => [$data['email']],
                    'options' => ['archive' => false],
                ]
            );

        $response->throw();

        return true;
    }
}
