<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| NOTIFICATION SERVICE — Port 8004
|--------------------------------------------------------------------------
|
| Handles: CRUD Notifications, Filter by Recipient
|
| Endpoint ini dipanggil oleh:
| - Order Service (via SendOrderNotificationJob) saat order dibuat
| - Admin/sistem untuk melihat dan mengelola notifikasi
|
*/

Route::get('/notifications', [NotificationController::class, 'index']);
Route::post('/notifications', [NotificationController::class, 'store']);
Route::get('/notifications/{id}', [NotificationController::class, 'show']);
Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
Route::get('/notifications/recipient/{recipient}', [NotificationController::class, 'byRecipient']);
