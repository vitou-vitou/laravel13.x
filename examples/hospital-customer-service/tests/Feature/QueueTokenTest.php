<?php

namespace Tests\Feature;

use App\Events\QueueTokenUpdated;
use App\Models\Department;
use App\Models\QueueToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class QueueTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_can_issue_queue_token_and_see_position(): void
    {
        Event::fake([QueueTokenUpdated::class]);

        $dept = Department::create([
            'name' => 'Cardiology',
            'code' => 'CARD',
            'counter_location' => 'Desk 3',
            'operating_hours' => '8AM - 5PM',
            'is_active' => true,
        ]);

        $res1 = $this->postJson('/api/v1/queue/tokens', [
            'department_id' => $dept->id,
            'patient_name' => 'John Doe',
            'patient_phone' => '+85512345678',
        ]);

        $res1->assertStatus(201);
        $res1->assertJsonPath('data.token_number', 'CARD-001');
        $res1->assertJsonPath('data.position_ahead', 0);
        $res1->assertJsonPath('data.estimated_wait_minutes', 8);

        $res2 = $this->postJson('/api/v1/queue/tokens', [
            'department_id' => $dept->id,
            'patient_name' => 'Jane Smith',
        ]);

        $res2->assertStatus(201);
        $res2->assertJsonPath('data.token_number', 'CARD-002');
        $res2->assertJsonPath('data.position_ahead', 1);
        $res2->assertJsonPath('data.estimated_wait_minutes', 16);

        Event::assertDispatched(QueueTokenUpdated::class, 2);
    }

    public function test_patient_can_lookup_queue_token_status(): void
    {
        $dept = Department::create([
            'name' => 'General Medicine',
            'code' => 'GEN',
            'counter_location' => 'Desk 1',
            'operating_hours' => '8AM - 5PM',
            'is_active' => true,
        ]);

        $token = QueueToken::create([
            'department_id' => $dept->id,
            'token_number' => 'GEN-005',
            'status' => 'called',
            'counter_assigned' => 'Counter 2',
            'called_at' => now(),
        ]);

        $response = $this->getJson('/api/v1/queue/tokens/GEN-005');

        $response->assertStatus(200);
        $response->assertJsonPath('data.token_number', 'GEN-005');
        $response->assertJsonPath('data.status', 'called');
        $response->assertJsonPath('data.counter_assigned', 'Counter 2');
    }

    public function test_staff_can_view_queue_and_call_next_patient(): void
    {
        Event::fake([QueueTokenUpdated::class]);

        $dept = Department::create([
            'name' => 'Pediatrics',
            'code' => 'PED',
            'counter_location' => 'Desk 2',
            'operating_hours' => '8AM - 5PM',
            'is_active' => true,
        ]);

        $token = QueueToken::create([
            'department_id' => $dept->id,
            'token_number' => 'PED-001',
            'status' => 'waiting',
        ]);

        $listRes = $this->getJson("/api/v1/staff/queue?department_id={$dept->id}");
        $listRes->assertStatus(200);
        $listRes->assertJsonCount(1, 'data');

        $callRes = $this->postJson("/api/v1/staff/queue/{$token->id}/call", [
            'counter' => 'Counter 102',
        ]);

        $callRes->assertStatus(200);
        $callRes->assertJsonPath('data.status', 'called');
        $callRes->assertJsonPath('data.counter_assigned', 'Counter 102');

        $this->assertDatabaseHas('queue_tokens', [
            'id' => $token->id,
            'status' => 'called',
            'counter_assigned' => 'Counter 102',
        ]);

        Event::assertDispatched(QueueTokenUpdated::class);
    }

    public function test_staff_can_transition_token_status_lifecycle(): void
    {
        Event::fake([QueueTokenUpdated::class]);

        $dept = Department::create([
            'name' => 'Eye Clinic',
            'code' => 'EYE',
            'counter_location' => 'Desk 4',
            'operating_hours' => '8AM - 5PM',
            'is_active' => true,
        ]);

        $token = QueueToken::create([
            'department_id' => $dept->id,
            'token_number' => 'EYE-001',
            'status' => 'called',
            'counter_assigned' => 'Counter 4',
        ]);

        $servingRes = $this->postJson("/api/v1/staff/queue/{$token->id}/status", [
            'status' => 'serving',
        ]);
        $servingRes->assertStatus(200);
        $this->assertDatabaseHas('queue_tokens', [
            'id' => $token->id,
            'status' => 'serving',
        ]);

        $completedRes = $this->postJson("/api/v1/staff/queue/{$token->id}/status", [
            'status' => 'completed',
        ]);
        $completedRes->assertStatus(200);
        $this->assertDatabaseHas('queue_tokens', [
            'id' => $token->id,
            'status' => 'completed',
        ]);
    }
}
