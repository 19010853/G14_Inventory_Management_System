<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $notifications = $user->notifications()->latest()->paginate(15);

        return view('admin.backend.notifications.index', compact('notifications'));
    }

    /**
     * Mark all unread notifications as read for the current user.
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return back();
    }
}

