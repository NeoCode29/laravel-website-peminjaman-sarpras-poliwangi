<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(private NotificationService $notificationService)
    {
        $this->middleware(['auth', 'user.not.blocked', 'profile.completed']);
    }

    public function index(Request $request): View
    {
        $filters = [
            'unread' => $request->boolean('unread', false),
            'type' => $request->query('type'),
            'hide_expired' => $request->boolean('hide_expired', true),
        ];

        $notifications = $this->notificationService->listForUser(
            $request->user()->id,
            array_filter($filters, fn ($v) => $v !== null)
        );

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Request $request, Notification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 403);
        $this->notificationService->markAsRead($notification);
        return back()->with('success', 'Notifikasi ditandai telah dibaca.');
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $count = $this->notificationService->markAllAsRead($request->user()->id);
        return back()->with('success', "Menandai $count notifikasi sebagai dibaca.");
    }

    public function show(Request $request, Notification $notification): \Illuminate\View\View
    {
        abort_unless($notification->user_id === $request->user()->id, 403);
        // Otomatis tandai dibaca saat dibuka
        $this->notificationService->markAsRead($notification);
        return view('notifications.show', compact('notification'));
    }

    public function click(Request $request, Notification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 403);
        $this->notificationService->markAsRead($notification);
        $target = $notification->action_url ?: route('notifications.index');
        return redirect()->to($target);
    }
}


