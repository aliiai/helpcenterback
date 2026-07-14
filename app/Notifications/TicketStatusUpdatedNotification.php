<?php

namespace App\Notifications;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TicketStatusUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public TicketStatus $previousStatus,
        public User $actor,
    ) {}

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
        $statusLabels = [
            TicketStatus::Open->value => 'مفتوحة',
            TicketStatus::InProgress->value => 'قيد المعالجة',
            TicketStatus::Closed->value => 'مغلقة',
        ];

        $newStatus = $statusLabels[$this->ticket->status->value] ?? $this->ticket->status->value;

        return [
            'type' => 'ticket_status_updated',
            'title' => 'تحديث حالة التذكرة',
            'message' => 'تم تغيير حالة «'.$this->ticket->title.'» إلى '.$newStatus.' بواسطة '.$this->actor->name,
            'ticket_id' => $this->ticket->id,
            'previous_status' => $this->previousStatus->value,
            'status' => $this->ticket->status->value,
            'actor_id' => $this->actor->id,
            'actor_name' => $this->actor->name,
        ];
    }
}
