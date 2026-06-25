<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

use App\Http\Middleware\VerifyUserServiceToken;

Route::get('/products', [ProductController::class, 'index']);

Route::middleware([VerifyUserServiceToken::class])->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
});

Route::post('/products/decrement', [ProductController::class, 'decrementStock']);
