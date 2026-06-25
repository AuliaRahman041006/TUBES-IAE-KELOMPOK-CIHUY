<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function getUserNotifications(Request $request)
    {
        $userId = $request->attributes->get('user_id');
        $notifications = Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        $formattedData = $notifications->map(function ($notif) {
            return [
                'pesan' => $notif->message,
                'waktu' => $notif->created_at->diffForHumans()
            ];
        });

        return response()->json([
            'message' => "Anda memiliki {$notifications->count()} pesan terbaru",
            'data' => $formattedData
        ]);
    }

    public function clearNotifications(Request $request)
    {
        $userId = $request->attributes->get('user_id');
        Notification::where('user_id', $userId)->delete();

        return response()->json([
            'message' => 'Semua riwayat notifikasi Anda telah berhasil dibersihkan.'
        ]);
    }
}
