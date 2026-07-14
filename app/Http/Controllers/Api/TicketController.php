<?php

namespace App\Http\Controllers\Api;

use App\Actions\NotifyAdminsAction;
use App\Actions\StoreAttachmentsAction;
use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreTicketRequest;
use App\Http\Requests\Api\UpdateTicketRequest;
use App\Http\Resources\Api\TicketResource;
use App\Models\Ticket;
use App\Notifications\TicketCreatedNotification;
use App\Notifications\TicketStatusUpdatedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class TicketController extends Controller
{
    public function __construct(
        private StoreAttachmentsAction $storeAttachments,
        private NotifyAdminsAction $notifyAdmins,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Ticket::class);

        $status = null;

        if ($request->filled('status')) {
            $status = TicketStatus::tryFrom($request->string('status')->toString());

            if ($status === null) {
                throw ValidationException::withMessages([
                    'status' => ['The selected status is invalid.'],
                ]);
            }
        }

        $tickets = Ticket::query()
            ->with(['user', 'attachments'])
            ->withCount('replies')
            ->visibleTo($request->user())
            ->when($status !== null, fn ($query) => $query->status($status))
            ->latest()
            ->paginate(15);

        return TicketResource::collection($tickets);
    }

    public function store(StoreTicketRequest $request): JsonResponse
    {
        $ticket = $request->user()->tickets()->create([
            ...$request->safe()->only(['title', 'description']),
            'status' => TicketStatus::Open,
        ]);

        if ($request->hasFile('images')) {
            $this->storeAttachments->handle(
                $ticket,
                $request->file('images'),
                'tickets/'.$ticket->id,
            );
        }

        $this->notifyAdmins->handle(new TicketCreatedNotification($ticket));

        $ticket->load(['user', 'attachments'])->loadCount('replies');

        return (new TicketResource($ticket))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Ticket $ticket): TicketResource
    {
        $this->authorize('view', $ticket);

        $ticket->load([
            'user',
            'attachments',
            'replies' => fn ($query) => $query->oldest(),
            'replies.user',
            'replies.attachments',
        ])->loadCount('replies');

        return new TicketResource($ticket);
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket): TicketResource
    {
        $previousStatus = $ticket->status;

        $ticket->update($request->validated());

        if ($previousStatus !== $ticket->status && $ticket->user_id !== $request->user()->id) {
            $ticket->user->notify(new TicketStatusUpdatedNotification(
                $ticket,
                $previousStatus,
                $request->user(),
            ));
        }

        $ticket->load(['user', 'attachments'])->loadCount('replies');

        return new TicketResource($ticket);
    }
}
