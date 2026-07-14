<?php

namespace Tests\Feature;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admins_are_notified_when_a_ticket_is_created(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->user()->create();

        Sanctum::actingAs($user);

        $this->postJson('/api/tickets', [
            'title' => 'مشكلة تسجيل الدخول',
            'description' => 'لا أستطيع الدخول إلى الحساب.',
        ])->assertCreated();

        $this->assertDatabaseCount('notifications', 1);

        Sanctum::actingAs($admin);

        $this->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('data.0.type', 'ticket_created')
            ->assertJsonCount(1, 'data');
    }

    public function test_ticket_owner_is_notified_when_admin_replies(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->user()->create();
        $ticket = Ticket::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($admin);

        $this->post("/api/tickets/{$ticket->id}/replies", [
            'body' => 'نعمل على حل المشكلة الآن.',
        ], ['Accept' => 'application/json'])->assertCreated();

        $this->assertSame(1, $user->fresh()->unreadNotifications()->count());
    }

    public function test_user_can_mark_notifications_as_read(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->user()->create();

        Sanctum::actingAs($user);

        $this->postJson('/api/tickets', [
            'title' => 'تذكرة اختبار',
            'description' => 'محتوى الاختبار',
        ])->assertCreated();

        Sanctum::actingAs($admin);

        $notificationId = $admin->fresh()->notifications()->first()->id;

        $this->postJson("/api/notifications/{$notificationId}/read")
            ->assertOk()
            ->assertJsonPath('data.is_read', true);

        $this->postJson('/api/notifications/read-all')
            ->assertOk()
            ->assertJsonPath('unread_count', 0);
    }

    public function test_admins_are_notified_on_user_registration(): void
    {
        $admin = User::factory()->admin()->create();

        $this->postJson('/api/register', [
            'name' => 'مستخدم جديد',
            'email' => 'new.user@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertCreated();

        $this->assertSame(1, $admin->fresh()->unreadNotifications()->count());
        $this->assertSame(
            'user_registered',
            $admin->fresh()->notifications()->first()->data['type'],
        );
    }

    public function test_owner_is_notified_when_ticket_status_changes(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->user()->create();
        $ticket = Ticket::factory()->open()->create(['user_id' => $user->id]);

        Sanctum::actingAs($admin);

        $this->patchJson("/api/tickets/{$ticket->id}", [
            'status' => TicketStatus::InProgress->value,
        ])->assertOk();

        $this->assertSame(1, $user->fresh()->unreadNotifications()->count());
        $this->assertSame(
            'ticket_status_updated',
            $user->fresh()->notifications()->first()->data['type'],
        );
    }
}
