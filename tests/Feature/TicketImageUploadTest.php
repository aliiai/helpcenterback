<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TicketImageUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_ticket_with_images(): void
    {
        Storage::fake('public');

        $user = User::factory()->user()->create();

        Sanctum::actingAs($user);

        $response = $this->post('/api/tickets', [
            'title' => 'Broken screenshot',
            'description' => 'See attached images.',
            'images' => [
                UploadedFile::fake()->image('error.png'),
                UploadedFile::fake()->image('details.jpg'),
            ],
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertCreated()
            ->assertJsonCount(2, 'data.attachments')
            ->assertJsonPath('data.attachments.0.mime_type', 'image/png');

        $ticket = Ticket::query()->first();

        $this->assertNotNull($ticket);
        $this->assertDatabaseCount('attachments', 2);
        Storage::disk('public')->assertExists($ticket->attachments->first()->path);
    }

    public function test_user_can_reply_with_images(): void
    {
        Storage::fake('public');

        $user = User::factory()->user()->create();
        $ticket = Ticket::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->post("/api/tickets/{$ticket->id}/replies", [
            'body' => 'Here is more context.',
            'images' => [
                UploadedFile::fake()->image('follow-up.webp'),
            ],
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertCreated()
            ->assertJsonCount(1, 'data.attachments');

        $this->assertDatabaseCount('attachments', 1);
    }

    public function test_ticket_rejects_non_image_uploads(): void
    {
        Storage::fake('public');

        $user = User::factory()->user()->create();

        Sanctum::actingAs($user);

        $response = $this->post('/api/tickets', [
            'title' => 'Invalid file',
            'description' => 'Should fail validation.',
            'images' => [
                UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf'),
            ],
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['images.0']);

        $this->assertDatabaseCount('tickets', 0);
        $this->assertDatabaseCount('attachments', 0);
    }
}
