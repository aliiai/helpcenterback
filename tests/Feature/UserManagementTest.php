<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_users(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->count(2)->create();

        Sanctum::actingAs($admin);

        $this->getJson('/api/users')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_standard_user_cannot_list_users(): void
    {
        $user = User::factory()->user()->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/users')->assertForbidden();
    }

    public function test_admin_can_create_a_user(): void
    {
        $admin = User::factory()->admin()->create();

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/users', [
            'name' => 'New Agent',
            'email' => 'agent@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'user',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.email', 'agent@example.com')
            ->assertJsonPath('data.role', 'user');

        $this->assertDatabaseHas('users', [
            'email' => 'agent@example.com',
            'role' => UserRole::User->value,
        ]);
    }

    public function test_admin_can_update_a_user(): void
    {
        $admin = User::factory()->admin()->create();
        $target = User::factory()->user()->create();

        Sanctum::actingAs($admin);

        $this->patchJson("/api/users/{$target->id}", [
            'name' => 'Updated Name',
            'role' => 'admin',
        ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.role', 'admin');
    }

    public function test_admin_can_delete_another_user(): void
    {
        $admin = User::factory()->admin()->create();
        $target = User::factory()->user()->create();

        Sanctum::actingAs($admin);

        $this->deleteJson("/api/users/{$target->id}")
            ->assertOk()
            ->assertJsonPath('message', 'User deleted successfully.');

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $admin = User::factory()->admin()->create();

        Sanctum::actingAs($admin);

        $this->deleteJson("/api/users/{$admin->id}")->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
