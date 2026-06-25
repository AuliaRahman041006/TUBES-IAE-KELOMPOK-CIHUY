<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificationController;

use App\Http\Middleware\VerifyUserServiceToken;

Route::middleware([VerifyUserServiceToken::class])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'getUserNotifications']);
    Route::delete('/notifications', [NotificationController::class, 'clearNotifications']);
});
