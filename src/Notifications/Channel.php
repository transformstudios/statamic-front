<?php

namespace TransformStudios\Front\Notifications;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Statamic\Auth\User;

class Channel
{
    /**
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function send(User $to, BaseNotification $notification): bool
    {
        $data = $this->data($to, $notification);

        if ($conversationId = Cache::get($key = $notification->key)) {
            Cache::forget($key);

            return $this->post('conversations', $conversationId, $data)->successful();
        }

        $response = $this->post('channels', config('front.notifications.channel'), $data);
        Cache::forever($key, $this->getConversationId($response));

        return $response->successful();
    }

    private function data(User $to, BaseNotification $notification): array
    {
        return [
            'body' => $notification->renderedView,
            'options' => ['archive' => false],
            'subject' => $notification->subject,
            'to' => $to->email(),
        ];
    }

    private function post(string $segment, string $id, array $data): Response
    {
        return Http::withToken(config('front.api_token'))
            ->baseUrl('https://api2.frontapp.com')
            ->post("/$segment/$id/messages", $data)
            ->throw();
    }

    private function getConversationId(Response $response): string
    {
        return last(explode(
            '/',
            Arr::get($response, '_links.related.conversation')
        ));
    }
}
