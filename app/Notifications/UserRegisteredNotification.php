<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserRegisteredNotification extends Notification
{
    use Queueable;

    public function __construct(public User $registeredUser) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $roleLabel = $this->registeredUser->isAdmin() ? 'مسؤول' : 'مستخدم';

        return [
            'type' => 'user_registered',
            'title' => 'تسجيل مستخدم جديد',
            'message' => 'انضم '.$this->registeredUser->name.' ('.$roleLabel.') إلى النظام',
            'user_id' => $this->registeredUser->id,
            'user_name' => $this->registeredUser->name,
            'user_email' => $this->registeredUser->email,
            'user_role' => $this->registeredUser->role->value,
        ];
    }
}
