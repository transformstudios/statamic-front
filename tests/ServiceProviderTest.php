<?php

use Statamic\Facades\User;
use Statamic\Statamic;

beforeEach(function () {
    config()->set('front.secret_key', 'secret');
    config()->set('front.chat.id', 'chat-id');
});

it('provides chat config to the control panel script for the current user', function () {
    $user = tap(User::make()->email('erin@transformstudios.com')->set('name', 'Erin'))->save();

    $this->actingAs($user);

    expect(Statamic::jsonVariables(request())['front'])->toBe([
        'chatId' => 'chat-id',
        'email' => 'erin@transformstudios.com',
        'name' => 'Erin',
        'userHash' => hash_hmac('sha256', 'erin@transformstudios.com', 'secret'),
    ]);
});

it('falls back to first and last name', function () {
    $user = tap(User::make()->email('erin@transformstudios.com')->set('first_name', 'Erin')->set('last_name', 'Dalzell'))->save();

    $this->actingAs($user);

    expect(Statamic::jsonVariables(request())['front']['name'])->toBe('Erin Dalzell');
});

it('provides nothing for a guest', function () {
    expect(Statamic::jsonVariables(request())['front'])->toBeNull();
});
