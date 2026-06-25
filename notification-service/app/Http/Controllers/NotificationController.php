<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of all notifications.
     * GET /api/notifications
     */
    public function index()
    {
        $notifications = Notification::latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => $notifications,
        ]);
    }

    /**
     * Store a newly created notification.
     * POST /api/notifications
     *
     * Expected body:
     * {
     *   "order_id": 1,
     *   "recipient": "John Doe",
     *   "type": "order_created",
     *   "title": "Order Berhasil Dibuat",
     *   "message": "Pesanan #1 senilai Rp 100.000 berhasil dibuat.",
     *   "data": {"order_id": 1, "total_price": 100000}  // optional, JSON
     * }
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id'  => 'nullable|integer',
            'recipient' => 'required|string|max:255',
            'type'      => 'required|string|in:order_created,status_changed,order_cancelled,general',
            'title'     => 'required|string|max:255',
            'message'   => 'required|string',
            'data'      => 'nullable|array',
        ]);

        $notification = Notification::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi berhasil dibuat',
            'data'    => $notification,
        ], 201);
    }

    /**
     * Display the specified notification.
     * GET /api/notifications/{id}
     */
    public function show($id)
    {
        $notification = Notification::findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $notification,
        ]);
    }

    /**
     * Remove the specified notification.
     * DELETE /api/notifications/{id}
     */
    public function destroy($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi berhasil dihapus',
        ]);
    }

    /**
     * Get notifications by recipient name.
     * GET /api/notifications/recipient/{recipient}
     */
    public function byRecipient($recipient)
    {
        $notifications = Notification::where('recipient', $recipient)
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => $notifications,
        ]);
    }

    /**
     * Mark notification as read.
     * PUT /api/notifications/{id}/read
     */
    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);

        if (!$notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi berhasil ditandai sebagai dibaca',
            'data'    => $notification,
        ]);
    }
}
