<?php

use Statamic\Facades\Antlers;
use Statamic\Facades\User;

beforeEach(function () {
    config()->set('front.secret_key', 'secret');
    config()->set('front.chat.id', 'chat-id');
    config()->set('front.chat.show_on_front_end', true);
});

it('renders the chat script for a guest', function () {
    $html = (string) Antlers::parse('{{ front:chat }}', trusted: true);

    expect($html)
        ->toContain('https://chat-assets.frontapp.com/v1/chat.bundle.js')
        ->toContain('chatId')
        ->toContain('chat-id')
        ->not->toContain('userHash');
});

it('identifies the current user', function () {
    $user = tap(User::make()->email('erin@transformstudios.com')->set('name', 'Erin'))->save();

    $this->actingAs($user);

    expect((string) Antlers::parse('{{ front:chat }}', trusted: true))
        ->toContain('erin@transformstudios.com')
        ->toContain('Erin')
        ->toContain(hash_hmac('sha256', 'erin@transformstudios.com', 'secret'));
});

it('renders nothing when hidden on the front end', function () {
    config()->set('front.chat.show_on_front_end', false);

    expect((string) Antlers::parse('{{ front:chat }}', trusted: true))->toBe('');
});

it('renders nothing without a chat id', function () {
    config()->set('front.chat.id', null);

    expect((string) Antlers::parse('{{ front:chat }}', trusted: true))->toBe('');
});
