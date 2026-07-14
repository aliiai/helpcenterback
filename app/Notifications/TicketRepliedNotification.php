<?php

namespace App\Notifications;

use App\Models\Reply;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TicketRepliedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public Reply $reply,
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
        $reply = $this->reply->loadMissing('user');
        $snippet = mb_strlen($reply->body) > 80
            ? mb_substr($reply->body, 0, 80).'…'
            : $reply->body;

        return [
            'type' => 'ticket_replied',
            'title' => 'رد جديد على تذكرة',
            'message' => $reply->user->name.' رد على «'.$this->ticket->title.'»: '.$snippet,
            'ticket_id' => $this->ticket->id,
            'reply_id' => $reply->id,
            'actor_id' => $reply->user_id,
            'actor_name' => $reply->user->name,
        ];
    }
}
