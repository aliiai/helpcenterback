<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Ticket $ticket): Response
    {
        return $this->ownsOrAdmin($user, $ticket)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    public function create(User $user): bool
    {
        return $user->isUser();
    }

    public function update(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin();
    }

    public function reply(User $user, Ticket $ticket): Response
    {
        return $this->ownsOrAdmin($user, $ticket)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    private function ownsOrAdmin(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin() || $ticket->user_id === $user->id;
    }
}
