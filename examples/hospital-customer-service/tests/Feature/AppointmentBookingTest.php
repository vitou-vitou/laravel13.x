<?php

namespace Tests\Feature;

use App\Models\AppointmentRequest;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_can_request_appointment_and_receive_tracking_code(): void
    {
        $dept = Department::create([
            'name' => 'Neurology',
            'code' => 'NEURO',
            'counter_location' => 'Building B, Floor 3',
            'operating_hours' => '8AM - 4PM',
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/appointments', [
            'department_id' => $dept->id,
            'patient_name' => 'Alice Walker',
            'patient_phone' => '+85599887766',
            'patient_email' => 'alice@example.com',
            'preferred_doctor' => 'Dr. Stephen',
            'preferred_date' => now()->addDays(2)->toDateString(),
            'preferred_time_slot' => 'morning',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.status', 'pending');
        $this->assertNotNull($response->json('data.tracking_code'));

        $trackingCode = $response->json('data.tracking_code');
        $this->assertDatabaseHas('appointment_requests', [
            'tracking_code' => $trackingCode,
            'patient_name' => 'Alice Walker',
            'status' => 'pending',
        ]);
    }

    public function test_patient_can_track_appointment_details(): void
    {
        $dept = Department::create([
            'name' => 'Dermatology',
            'code' => 'DERM',
            'counter_location' => 'Building A, Floor 1',
            'operating_hours' => '8AM - 5PM',
            'is_active' => true,
        ]);

        $apt = AppointmentRequest::create([
            'tracking_code' => 'APT-DERM123',
            'department_id' => $dept->id,
            'patient_name' => 'Robert Paul',
            'patient_phone' => '+85512000111',
            'preferred_date' => now()->addDays(1)->toDateString(),
            'preferred_time_slot' => 'afternoon',
            'status' => 'confirmed',
            'confirmed_date' => now()->addDays(1)->toDateString(),
            'confirmed_time_slot' => '14:30 PM',
            'assigned_room' => 'Consultation Room 104',
            'staff_notes' => 'Please arrive 15 minutes early with ID.',
        ]);

        $response = $this->getJson('/api/v1/appointments/APT-DERM123');

        $response->assertStatus(200);
        $response->assertJsonPath('data.tracking_code', 'APT-DERM123');
        $response->assertJsonPath('data.department', 'Dermatology');
        $response->assertJsonPath('data.status', 'confirmed');
        $response->assertJsonPath('data.assigned_room', 'Consultation Room 104');
    }

    public function test_staff_can_confirm_reschedule_and_cancel_appointments(): void
    {
        $dept = Department::create([
            'name' => 'ENT Clinic',
            'code' => 'ENT',
            'counter_location' => 'Building C, Floor 1',
            'operating_hours' => '8AM - 5PM',
            'is_active' => true,
        ]);

        $apt = AppointmentRequest::create([
            'tracking_code' => 'APT-ENT999',
            'department_id' => $dept->id,
            'patient_name' => 'Michael Chen',
            'patient_phone' => '+85588776655',
            'preferred_date' => now()->addDays(3)->toDateString(),
            'preferred_time_slot' => 'morning',
            'status' => 'pending',
        ]);

        $listRes = $this->getJson("/api/v1/staff/appointments?department_id={$dept->id}&status=pending");
        $listRes->assertStatus(200);
        $listRes->assertJsonCount(1, 'data');

        $confirmRes = $this->postJson("/api/v1/staff/appointments/{$apt->id}/confirm", [
            'confirmed_date' => now()->addDays(3)->toDateString(),
            'confirmed_time_slot' => '09:00 AM',
            'assigned_room' => 'Room 202',
            'staff_notes' => 'Confirmed with Dr. Davis',
        ]);

        $confirmRes->assertStatus(200);
        $this->assertDatabaseHas('appointment_requests', [
            'id' => $apt->id,
            'status' => 'confirmed',
            'assigned_room' => 'Room 202',
        ]);

        $rescheduleRes = $this->postJson("/api/v1/staff/appointments/{$apt->id}/reschedule", [
            'confirmed_date' => now()->addDays(4)->toDateString(),
            'confirmed_time_slot' => '11:00 AM',
            'staff_notes' => 'Doctor requested shift to next morning',
        ]);

        $rescheduleRes->assertStatus(200);
        $this->assertDatabaseHas('appointment_requests', [
            'id' => $apt->id,
            'status' => 'rescheduled',
        ]);

        $cancelRes = $this->postJson("/api/v1/staff/appointments/{$apt->id}/cancel", [
            'staff_notes' => 'Patient called to cancel visit',
        ]);

        $cancelRes->assertStatus(200);
        $this->assertDatabaseHas('appointment_requests', [
            'id' => $apt->id,
            'status' => 'cancelled',
        ]);
    }
}
