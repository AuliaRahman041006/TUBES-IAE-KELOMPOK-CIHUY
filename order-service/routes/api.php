<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

use App\Http\Middleware\VerifyUserServiceToken;

Route::middleware([VerifyUserServiceToken::class])->group(function () {
    Route::post('/orders', [OrderController::class, 'store']);
});
