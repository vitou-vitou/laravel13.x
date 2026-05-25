<?php

namespace Tests\Unit;

use App\Models\Todo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_status_options_returns_expected_values(): void
    {
        $this->assertEquals(['pending', 'in_progress', 'completed'], Todo::getStatusOptions());
    }

    public function test_get_priority_options_returns_expected_values(): void
    {
        $this->assertEquals(['low', 'medium', 'high'], Todo::getPriorityOptions());
    }

    public function test_mark_complete_sets_status_and_timestamp(): void
    {
        $todo = Todo::factory()->create(['status' => 'pending', 'completed_at' => null]);

        $todo->markComplete();

        $this->assertEquals('completed', $todo->fresh()->status);
        $this->assertNotNull($todo->fresh()->completed_at);
    }

    public function test_mark_incomplete_resets_status_and_clears_timestamp(): void
    {
        $todo = Todo::factory()->create(['status' => 'completed', 'completed_at' => now()]);

        $todo->markIncomplete();

        $this->assertEquals('pending', $todo->fresh()->status);
        $this->assertNull($todo->fresh()->completed_at);
    }

    public function test_scope_by_status_filters_correctly(): void
    {
        Todo::factory()->create(['status' => 'pending']);
        Todo::factory()->create(['status' => 'completed']);

        $results = Todo::byStatus('pending')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('pending', $results->first()->status);
    }

    public function test_scope_by_priority_filters_correctly(): void
    {
        Todo::factory()->create(['priority' => 'high']);
        Todo::factory()->create(['priority' => 'low']);

        $results = Todo::byPriority('high')->get();

        $this->assertCount(1, $results);
    }

    public function test_scope_search_matches_title(): void
    {
        Todo::factory()->create(['title' => 'Alpha task']);
        Todo::factory()->create(['title' => 'Beta task']);

        $results = Todo::search('Alpha')->get();

        $this->assertCount(1, $results);
    }

    public function test_scope_search_matches_description(): void
    {
        Todo::factory()->create(['description' => 'needs groceries']);
        Todo::factory()->create(['description' => 'needs coffee']);

        $results = Todo::search('groceries')->get();

        $this->assertCount(1, $results);
    }

    public function test_due_date_cast_to_date(): void
    {
        $todo = Todo::factory()->create(['due_date' => '2030-01-15']);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $todo->due_date);
    }

    public function test_completed_at_cast_to_datetime(): void
    {
        $todo = Todo::factory()->create(['completed_at' => now()]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $todo->completed_at);
    }
}
