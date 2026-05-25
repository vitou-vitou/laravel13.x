<?php

namespace Tests\Feature;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoControllerTest extends TestCase
{
    use RefreshDatabase;

    private function auth(): static
    {
        return $this->actingAs(User::factory()->create(), 'api');
    }

    // index (public)

    public function test_index_returns_paginated_todos(): void
    {
        Todo::factory()->count(3)->create();

        $this->getJson('/api/v1/todos')
            ->assertStatus(200)
            ->assertJsonStructure(['data', 'meta', 'links']);
    }

    public function test_index_filters_by_valid_status(): void
    {
        Todo::factory()->create(['status' => 'pending']);
        Todo::factory()->create(['status' => 'completed']);

        $response = $this->getJson('/api/v1/todos?status=pending');

        $data = $response->assertStatus(200)->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('pending', $data[0]['status']);
    }

    public function test_index_ignores_invalid_status_filter(): void
    {
        Todo::factory()->count(2)->create();

        $data = $this->getJson('/api/v1/todos?status=invalid')
            ->assertStatus(200)
            ->json('data');

        $this->assertCount(2, $data);
    }

    public function test_index_filters_by_priority(): void
    {
        Todo::factory()->create(['priority' => 'high']);
        Todo::factory()->create(['priority' => 'low']);

        $data = $this->getJson('/api/v1/todos?priority=high')
            ->assertStatus(200)
            ->json('data');

        $this->assertCount(1, $data);
    }

    public function test_index_searches_by_title(): void
    {
        Todo::factory()->create(['title' => 'Buy groceries']);
        Todo::factory()->create(['title' => 'Write tests']);

        $data = $this->getJson('/api/v1/todos?search=groceries')
            ->assertStatus(200)
            ->json('data');

        $this->assertCount(1, $data);
    }

    public function test_index_searches_by_description(): void
    {
        Todo::factory()->create(['description' => 'from the supermarket']);
        Todo::factory()->create(['description' => 'unit tests']);

        $data = $this->getJson('/api/v1/todos?search=supermarket')
            ->assertStatus(200)
            ->json('data');

        $this->assertCount(1, $data);
    }

    // show (public)

    public function test_show_returns_todo(): void
    {
        $todo = Todo::factory()->create();

        $this->getJson("/api/v1/todos/{$todo->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.id', $todo->id);
    }

    public function test_show_returns_404_for_missing_todo(): void
    {
        $this->getJson('/api/v1/todos/9999')->assertStatus(404);
    }

    // store (auth required)

    public function test_store_creates_todo(): void
    {
        $this->auth()
            ->postJson('/api/v1/todos', ['title' => 'New task', 'priority' => 'high'])
            ->assertStatus(201)
            ->assertJsonPath('data.title', 'New task');

        $this->assertDatabaseHas('todos', ['title' => 'New task']);
    }

    public function test_store_requires_auth(): void
    {
        $this->postJson('/api/v1/todos', ['title' => 'Task'])->assertStatus(401);
    }

    public function test_store_fails_without_title(): void
    {
        $this->auth()
            ->postJson('/api/v1/todos', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_store_fails_with_invalid_status(): void
    {
        $this->auth()
            ->postJson('/api/v1/todos', ['title' => 'Task', 'status' => 'unknown'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_store_fails_with_invalid_priority(): void
    {
        $this->auth()
            ->postJson('/api/v1/todos', ['title' => 'Task', 'priority' => 'critical'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['priority']);
    }

    public function test_store_fails_with_past_due_date(): void
    {
        $this->auth()
            ->postJson('/api/v1/todos', [
                'title' => 'Task',
                'due_date' => now()->subDay()->toDateString(),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['due_date']);
    }

    // update (auth required)

    public function test_update_modifies_todo(): void
    {
        $todo = Todo::factory()->create(['title' => 'Old']);

        $this->auth()
            ->putJson("/api/v1/todos/{$todo->id}", ['title' => 'New'])
            ->assertStatus(200)
            ->assertJsonPath('data.title', 'New');

        $this->assertDatabaseHas('todos', ['id' => $todo->id, 'title' => 'New']);
    }

    public function test_update_requires_auth(): void
    {
        $todo = Todo::factory()->create();

        $this->putJson("/api/v1/todos/{$todo->id}", ['title' => 'X'])->assertStatus(401);
    }

    public function test_update_fails_with_invalid_status(): void
    {
        $todo = Todo::factory()->create();

        $this->auth()
            ->putJson("/api/v1/todos/{$todo->id}", ['status' => 'bogus'])
            ->assertStatus(422);
    }

    // destroy (auth required)

    public function test_destroy_deletes_todo(): void
    {
        $todo = Todo::factory()->create();

        $this->auth()
            ->deleteJson("/api/v1/todos/{$todo->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('todos', ['id' => $todo->id]);
    }

    public function test_destroy_requires_auth(): void
    {
        $todo = Todo::factory()->create();

        $this->deleteJson("/api/v1/todos/{$todo->id}")->assertStatus(401);
    }

    // complete / incomplete (auth required)

    public function test_complete_marks_todo_done(): void
    {
        $todo = Todo::factory()->create(['status' => 'pending']);

        $this->auth()
            ->patchJson("/api/v1/todos/{$todo->id}/complete")
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'completed');

        $this->assertNotNull($todo->fresh()->completed_at);
    }

    public function test_incomplete_resets_todo(): void
    {
        $todo = Todo::factory()->create(['status' => 'completed', 'completed_at' => now()]);

        $this->auth()
            ->patchJson("/api/v1/todos/{$todo->id}/incomplete")
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'pending');

        $this->assertNull($todo->fresh()->completed_at);
    }
}
