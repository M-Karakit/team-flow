<?php

namespace App\Http\Controllers\Api\Notification;

use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    public function index() {
        return response()->json([
            'notifications' => auth('api')->user()->unreadNotifications,
            'total_unread'  => auth('api')->user()->unreadNotifications->count(),
        ]);
    }

    public function markAsRead(string $id) {
        $notification = auth('api')->user()
                              ->notifications()
                              ->findOrFail($id);

        $notification->markAsRead();

        return response()->json(['message' => 'Notification marked as read.']);
    }

    public function markAllAsRead() {
        auth('api')->user()->unreadNotifications->markAsRead();

        return response()->json(['message' => 'All notifications marked as read.']);
    }

    public function destroy(string $id) {
        auth('api')->user()
              ->notifications()
              ->findOrFail($id)
              ->delete();

        return response()->json(['message' => 'Notification deleted.']);
    }
}
