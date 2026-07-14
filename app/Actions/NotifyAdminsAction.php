<?php

namespace App\Actions;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class NotifyAdminsAction
{
    public function handle(Notification $notification, ?int $exceptUserId = null): void
    {
        $admins = $this->admins($exceptUserId);

        if ($admins->isEmpty()) {
            return;
        }

        NotificationFacade::send($admins, $notification);
    }

    /**
     * @return Collection<int, User>
     */
    private function admins(?int $exceptUserId): Collection
    {
        return User::query()
            ->where('role', UserRole::Admin)
            ->when(
                $exceptUserId !== null,
                fn ($query) => $query->where('id', '!=', $exceptUserId),
            )
            ->get();
    }
}
