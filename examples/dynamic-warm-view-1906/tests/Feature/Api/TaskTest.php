<?php

namespace Tests\Feature\Api;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_crud_own_tasks(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $create = $this->postJson('/api/tasks', [
            'title' => 'Ship MVP',
        ]);

        $create->assertCreated()
            ->assertJson(['title' => 'Ship MVP', 'completed' => false]);

        $taskId = $create->json('id');

        $this->getJson('/api/tasks')
            ->assertOk()
            ->assertJsonCount(1);

        $this->patchJson("/api/tasks/{$taskId}", [
            'completed' => true,
        ])->assertOk()
            ->assertJson(['completed' => true]);

        $this->deleteJson("/api/tasks/{$taskId}")
            ->assertNoContent();

        $this->assertDatabaseMissing('tasks', ['id' => $taskId]);
    }

    public function test_user_cannot_access_another_users_task(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $task = Task::factory()->for($owner)->create();

        Sanctum::actingAs($intruder);

        $this->getJson("/api/tasks/{$task->id}")->assertForbidden();
        $this->patchJson("/api/tasks/{$task->id}", ['title' => 'Nope'])->assertForbidden();
        $this->deleteJson("/api/tasks/{$task->id}")->assertForbidden();
    }
}
