<?php

namespace Tests\Feature;

use App\Models\ShipNote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShipNoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_week_board(): void
    {
        ShipNote::query()->create([
            'weekday' => 'mon',
            'title' => 'SEA day',
            'region' => 'Southeast Asia',
            'company_habit' => 'Pragmatism',
            'project_type' => 'Internal tools',
            'practice' => 'Tried one habit',
            'verdict' => 'keep',
        ]);

        $this->get(route('ship.index'))
            ->assertOk()
            ->assertSee('7-day ship board')
            ->assertSee('SEA day');
    }

    public function test_store_creates_note(): void
    {
        $this->post(route('ship.store'), [
            'weekday' => 'thu',
            'title' => 'Practice slice',
            'region' => 'Southeast Asia',
            'company_habit' => 'Shape Up appetite',
            'project_type' => 'Admin CRUD',
            'practice' => 'Built create form',
            'verdict' => 'keep',
        ])->assertRedirect(route('ship.index'));

        $this->assertDatabaseHas('ship_notes', [
            'title' => 'Practice slice',
            'weekday' => 'thu',
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->from(route('ship.create'))
            ->post(route('ship.store'), [])
            ->assertRedirect(route('ship.create'))
            ->assertSessionHasErrors(['weekday', 'title', 'region', 'company_habit', 'project_type', 'verdict']);
    }
}
