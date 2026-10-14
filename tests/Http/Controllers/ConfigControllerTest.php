<?php

use Statamic\Facades\User;

beforeEach(function () {
    config()->set('front.secret_key', 'secret');
    config()->set('front.chat.id', 'chat-id');
    config()->set('front.chat.show_on_front_end', true);
});

it('returns user details when configured', function () {
    $user = tap(User::make()->email('erin@transformstudios.com')->set('name', 'Erin'))->save();

    $this->actingAs($user)
        ->getJson('/!/front/config')
        ->assertOk()
        ->assertExactJson([
            'configured' => true,
            'chatId' => 'chat-id',
            'email' => 'erin@transformstudios.com',
            'name' => 'Erin',
            'hash' => hash_hmac('sha256', 'erin@transformstudios.com', 'secret'),
        ]);
});

it('returns nulls for a guest', function () {
    $this->getJson('/!/front/config')
        ->assertOk()
        ->assertJson(['configured' => true, 'email' => null, 'name' => null, 'hash' => null]);
});

it('is not configured when hidden on the front end', function () {
    config()->set('front.chat.show_on_front_end', false);

    $this->getJson('/!/front/config')->assertOk()->assertJson(['configured' => false]);
});
