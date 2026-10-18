<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\ReportStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_can_check_report_readiness_with_verification(): void
    {
        $dept = Department::create([
            'name' => 'Biochemistry Lab',
            'code' => 'BIO',
            'counter_location' => 'Building B, Counter 202',
            'operating_hours' => '24 Hours',
            'is_active' => true,
        ]);

        ReportStatus::create([
            'order_number' => 'LAB-2026-8888',
            'phone_last_four' => '5678',
            'department_id' => $dept->id,
            'test_category' => 'Complete Blood Count & Liver Panel',
            'status' => 'ready_for_pickup',
            'pickup_counter' => 'Counter 202 - Lab Records Window',
            'ready_at' => now(),
        ]);

        $successRes = $this->postJson('/api/v1/reports/check', [
            'order_number' => 'LAB-2026-8888',
            'phone_last_four' => '5678',
        ]);

        $successRes->assertStatus(200);
        $successRes->assertJsonPath('data.status', 'ready_for_pickup');
        $successRes->assertJsonPath('data.pickup_counter', 'Counter 202 - Lab Records Window');
        $successRes->assertSee('Clinical measurements and diagnoses are never displayed online');

        $wrongPhoneRes = $this->postJson('/api/v1/reports/check', [
            'order_number' => 'LAB-2026-8888',
            'phone_last_four' => '0000',
        ]);

        $wrongPhoneRes->assertStatus(404);
    }

    public function test_staff_can_manage_report_readiness_lifecycle(): void
    {
        $dept = Department::create([
            'name' => 'Ultrasound Unit',
            'code' => 'US',
            'counter_location' => 'Building A, Room 108',
            'operating_hours' => '8AM - 5PM',
            'is_active' => true,
        ]);

        $createRes = $this->postJson('/api/v1/staff/reports', [
            'order_number' => 'US-9911',
            'phone_last_four' => '1234',
            'department_id' => $dept->id,
            'test_category' => 'Pelvic Ultrasound',
            'status' => 'in_analysis',
        ]);

        $createRes->assertStatus(201);
        $reportId = $createRes->json('data.id');

        $updateRes = $this->putJson("/api/v1/staff/reports/{$reportId}", [
            'status' => 'ready_for_pickup',
            'pickup_counter' => 'Desk 108 Window A',
        ]);

        $updateRes->assertStatus(200);
        $this->assertDatabaseHas('report_statuses', [
            'id' => $reportId,
            'status' => 'ready_for_pickup',
            'pickup_counter' => 'Desk 108 Window A',
        ]);
    }
}
