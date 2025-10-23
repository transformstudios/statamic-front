<?php

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

function front(): PendingRequest
{
    return Http::withToken(config('front.api_token'))
        ->baseUrl('https://api2.frontapp.com');
}
