<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\FCMService;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'data' => $notifications,
            'unread_count' => $user->unreadNotifications()->count()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:'.implode(',', [
                'booking_created',
                'booking_accepted',
                'booking_rejected',
                'booking_cancelled',
                'booking_completed',
                'payment_success',
                'payment_failed',
                'reminder',
                'review_added',
                'system_alert'
            ]),
            'booking_id' => 'nullable|exists:bookings,id',
            'data' => 'sometimes|array'
        ]);

        $user = User::find($validated['user_id']);

        $notification = $user->notifications()->create([
            'sender_id' => Auth::id(),
            'booking_id' => $validated['booking_id'] ?? null,
            'type' => $validated['type'],
            'title' => $validated['title'],
            'message' => $validated['body'],
        ]);

        if ($user->device_token) {
            $fcmData = [
                'title' => $validated['title'],
                'body' => $validated['body'],
                'data' => array_merge($validated['data'] ?? [], [
                    'type' => $validated['type'],
                    'notification_id' => $notification->id,
                    'booking_id' => $validated['booking_id'] ?? null,
                ])
            ];

            FCMService::send(
                $fcmData['title'],
                $fcmData['body'],
                $user->device_token,
            );
        }

        return response()->json([
            'success' => true,
            'data' => $notification,
            'message' => 'Notificación enviada y guardada correctamente'
        ], 201);
    }

    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notificación marcada como leída'
        ]);
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications()->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Todas las notificaciones marcadas como leídas'
        ]);
    }

    public function destroy($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notificación eliminada'
        ]);
    }
}