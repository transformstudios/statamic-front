<?php

namespace TransformStudios\Front\Notifications;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Statamic\Auth\User;

class Channel
{
    /**
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function send($ignore, BaseNotification $notification): bool
    {
        $data = $this->data($notification);

        if ($conversationId = Cache::pull($key = $notification->key)) {
            return $this->post('conversations', $conversationId, $data)->successful();
        }

        $response = $this->post('channels', config('front.notifications.channel'), $data);
        Cache::forever($key, $this->getConversationId($response));

        return $response->successful();
    }

    private function data(BaseNotification $notification): array
    {
        return [
            'body' => $notification->renderedView,
            'options' => ['archive' => false],
            'subject' => $notification->subject,
            'to' => $notification->users->map(fn (User $user) => $user->email())->all(),
        ];
    }

    private function post(string $segment, string $id, array $data): Response
    {
        return front()
            ->post("/$segment/$id/messages", $data)
            ->throw();
    }

    private function getConversationId(Response $response): string
    {
        return last(explode('/', Arr::get($response, '_links.related.conversation')));
    }
}
