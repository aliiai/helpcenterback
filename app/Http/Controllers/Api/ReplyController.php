<?php

namespace App\Http\Controllers\Api;

use App\Actions\NotifyAdminsAction;
use App\Actions\StoreAttachmentsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreReplyRequest;
use App\Http\Resources\Api\ReplyResource;
use App\Models\Ticket;
use App\Notifications\TicketRepliedNotification;
use Illuminate\Http\JsonResponse;

class ReplyController extends Controller
{
    public function __construct(
        private StoreAttachmentsAction $storeAttachments,
        private NotifyAdminsAction $notifyAdmins,
    ) {}

    public function store(StoreReplyRequest $request, Ticket $ticket): JsonResponse
    {
        $reply = $ticket->replies()->create([
            'user_id' => $request->user()->id,
            'body' => $request->validated('body'),
        ]);

        if ($request->hasFile('images')) {
            $this->storeAttachments->handle(
                $reply,
                $request->file('images'),
                'replies/'.$reply->id,
            );
        }

        $notification = new TicketRepliedNotification($ticket, $reply);

        if ($request->user()->isAdmin()) {
            if ($ticket->user_id !== $request->user()->id) {
                $ticket->user->notify($notification);
            }
        } else {
            $this->notifyAdmins->handle($notification);
        }

        $reply->load(['user', 'attachments']);

        return (new ReplyResource($reply))
            ->response()
            ->setStatusCode(201);
    }
}
