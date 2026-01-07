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
     * Mark a single notification as read and redirect to its target link.
     */
    public function redirectToTarget(Request $request, string $id)
    {
        $user = $request->user();
        $notification = $user->notifications()->findOrFail($id);

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        $link = $notification->data['link'] ?? url('/');

        return redirect($link);
    }
}

