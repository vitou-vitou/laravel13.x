<?php

namespace Tests\Feature;

use App\Filament\Resources\DemoItems\DemoItemResource;
use App\Models\DemoItem;
use App\Models\Guest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoItemIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_see_other_guest_demo_items_in_index(): void
    {
        $guestA = Guest::factory()->create();
        $guestB = Guest::factory()->create();

        DemoItem::factory()->create([
            'guest_id' => $guestA->id,
            'title' => 'Alpha secret',
        ]);
        DemoItem::factory()->create([
            'guest_id' => $guestB->id,
            'title' => 'Beta other',
        ]);

        $this->actingAs($guestA, 'guest');

        $response = $this->get(DemoItemResource::getUrl('index'));

        $response->assertOk();
        $response->assertSee('Alpha secret');
        $response->assertDontSee('Beta other');
    }
}
