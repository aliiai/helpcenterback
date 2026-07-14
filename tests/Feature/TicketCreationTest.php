<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TicketCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_a_ticket(): void
    {
        $user = User::factory()->user()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tickets', [
            'title' => 'Login issue',
            'description' => 'I cannot sign in with my credentials.',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Login issue')
            ->assertJsonPath('data.description', 'I cannot sign in with my credentials.')
            ->assertJsonPath('data.status', 'open')
            ->assertJsonPath('data.user.id', $user->id);

        $this->assertDatabaseHas('tickets', [
            'user_id' => $user->id,
            'title' => 'Login issue',
            'status' => 'open',
        ]);
    }

    public function test_guest_cannot_create_a_ticket(): void
    {
        $response = $this->postJson('/api/tickets', [
            'title' => 'Login issue',
            'description' => 'I cannot sign in with my credentials.',
        ]);

        $response->assertUnauthorized();

        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_ticket_creation_requires_valid_payload(): void
    {
        $user = User::factory()->user()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tickets', [
            'title' => '',
            'description' => '',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'description']);

        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_admin_cannot_create_a_ticket(): void
    {
        $admin = User::factory()->admin()->create([
            'role' => UserRole::Admin,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/tickets', [
            'title' => 'Admin ticket',
            'description' => 'Admins should not create tickets.',
        ]);

        $response->assertForbidden();

        $this->assertDatabaseCount('tickets', 0);
    }
}
