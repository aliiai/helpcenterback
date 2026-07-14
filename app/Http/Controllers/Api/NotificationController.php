<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->latest()
            ->limit(5)
            ->get();

        return response()->json([
            'data' => NotificationResource::collection($notifications),
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    public function markAsRead(Request $request, string $notification): NotificationResource
    {
        $model = $this->findOwnedNotification($request, $notification);
        $model->markAsRead();

        return new NotificationResource($model->fresh());
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'message' => 'تم تعيين جميع الإشعارات كمقروءة.',
            'unread_count' => 0,
        ]);
    }

    private function findOwnedNotification(Request $request, string $notificationId): DatabaseNotification
    {
        return $request->user()
            ->notifications()
            ->where('id', $notificationId)
            ->firstOrFail();
    }
}
