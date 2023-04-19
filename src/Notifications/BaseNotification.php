<?php

namespace TransformStudios\Front\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class BaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $key,
        public string $subject,
        public string $renderedView,
        public Collection $users)
    {
    }

    public function via($notifiable)
    {
        return ['front'];
    }
}
