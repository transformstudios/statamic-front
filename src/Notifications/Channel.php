<?php

namespace TransformStudios\Front\Notifications;

use Illuminate\Http\Client\Response;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Statamic\Auth\User;

class Channel
{
    /**
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function send($notifiable, Notification $notification):  bool
    {
        $data = $this->data($notification);

        $alertId = Arr::get($notification->toArray(), 'id');

        if ($conversationId = Cache::get($alertId)) {
            Cache::forget($alertId);

            return $this->post('conversations', $conversationId, $data)->successful();
        }

        $response = $this->post('channels', config('front.notifications.channel'), $data);
        Cache::forever($alertId, $this->getConversationId($response));

        return $response->successful();
    }

    private function data($notification): array
    {
        $data = $notification->toArray();

        $emails = $data['users']->map(fn (User $user) => $user->email());

        return [
            'body' => view("front::notifications.{$data['event']}", $data)->render(),
            'options' => ['archive' => false],
            'subject' => $data['subject'],
            'to' => $emails,
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

    private function removeConversationId(User $user, Notification $notification)
    {
        if (Arr::get($notification->toArray(), 'is_up')) {
            $user->remove('conversation_id')->save();
        }
    }

    private function saveConversationId(User $user, Response $response)
    {
        $user->set('conversation_id', $this->getConversationId($response))->save();
    }
}
