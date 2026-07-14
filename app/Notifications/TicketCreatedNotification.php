<?php

namespace App\Notifications;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TicketCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(public Ticket $ticket) {}

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
        $ticket = $this->ticket->loadMissing('user');

        return [
            'type' => 'ticket_created',
            'title' => 'تذكرة جديدة',
            'message' => 'قام '.$ticket->user->name.' بإنشاء تذكرة: '.$ticket->title,
            'ticket_id' => $ticket->id,
            'actor_id' => $ticket->user_id,
            'actor_name' => $ticket->user->name,
        ];
    }
}
