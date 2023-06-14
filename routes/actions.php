<?php

use Illuminate\Support\Facades\Route;
use TransformStudios\Front\Http\Controllers\ConfigController;

Route::get('config', ConfigController::class)->name('front.config');
