<?php

namespace Tests\Feature;

use App\Models\HospitalDepartment;
use App\Models\HospitalInquiry;
use App\Models\HospitalInquiryResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HospitalDeskTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_desk_dashboard_displays_inquiries_and_metrics(): void
    {
        $inquiry = HospitalInquiry::create([
            'ticket_code' => 'HOSP-2026-DESK1',
            'patient_name' => 'Alice Walker',
            'email' => 'alice@example.com',
            'phone' => '+1 (555) 321-7654',
            'category' => 'complaint',
            'urgency' => 'critical',
            'subject' => 'Long wait time in room 4B',
            'message' => 'I have been waiting for over 3 hours to see an attending physician.',
            'status' => 'open',
        ]);

        $response = $this->get('/hospital/desk');

        $response->assertOk();
        $response->assertSee('Hospital Customer Service Dashboard');
        $response->assertSee('HOSP-2026-DESK1');
        $response->assertSee('Alice Walker');
        $response->assertSee('critical');
    }

    public function test_desk_show_displays_all_responses_including_internal_notes(): void
    {
        $inquiry = HospitalInquiry::create([
            'ticket_code' => 'HOSP-2026-DESK2',
            'patient_name' => 'Brian Kernighan',
            'email' => 'brian@example.com',
            'phone' => '+1 (555) 999-8888',
            'category' => 'records',
            'urgency' => 'routine',
            'subject' => 'Need discharge summary signed',
            'message' => 'Need official copy for insurance claim filing.',
            'status' => 'open',
        ]);

        HospitalInquiryResponse::create([
            'inquiry_id' => $inquiry->id,
            'author_name' => 'Archivist Nurse',
            'response_text' => 'INTERNAL NOTE: Paper records located in storage box #40.',
            'is_internal' => true,
        ]);

        $response = $this->get('/hospital/desk/ticket/HOSP-2026-DESK2');

        $response->assertOk();
        $response->assertSee('HOSP-2026-DESK2');
        $response->assertSee('INTERNAL NOTE: Paper records located in storage box #40.');
        $response->assertSee('Internal Note');
    }

    public function test_staff_can_respond_and_resolve_ticket(): void
    {
        $inquiry = HospitalInquiry::create([
            'ticket_code' => 'HOSP-2026-DESK3',
            'patient_name' => 'Clara Barton',
            'email' => 'clara@example.com',
            'phone' => '+1 (555) 777-6666',
            'category' => 'pharmacy',
            'urgency' => 'urgent',
            'subject' => 'Insulin refill inquiry',
            'message' => 'Pharmacy told me the brand is out of stock.',
            'status' => 'open',
        ]);

        $payload = [
            'author_name' => 'Senior Pharmacist Dave',
            'response_text' => 'We have prepared an equivalent prescription available at Desk 1.',
            'is_internal' => 0,
            'new_status' => 'resolved',
        ];

        $response = $this->post("/hospital/desk/ticket/{$inquiry->ticket_code}/respond", $payload);

        $response->assertRedirect(route('hospital.desk.show', $inquiry->ticket_code));

        $this->assertDatabaseHas('hospital_inquiry_responses', [
            'inquiry_id' => $inquiry->id,
            'author_name' => 'Senior Pharmacist Dave',
            'is_internal' => 0,
        ]);

        $inquiry->refresh();
        $this->assertEquals('resolved', $inquiry->status);
        $this->assertNotNull($inquiry->resolved_at);
    }

    public function test_staff_can_update_status_and_administrative_notes(): void
    {
        $inquiry = HospitalInquiry::create([
            'ticket_code' => 'HOSP-2026-DESK4',
            'patient_name' => 'David Copperfield',
            'email' => 'david@example.com',
            'phone' => '+1 (555) 555-1212',
            'category' => 'general',
            'urgency' => 'routine',
            'subject' => 'Parking voucher validation',
            'message' => 'Where do I get my ticket validated?',
            'status' => 'open',
        ]);

        $payload = [
            'status' => 'in_progress',
            'staff_notes' => 'Patient called front desk on 10/07. Advised to visit cashier desk.',
        ];

        $response = $this->post("/hospital/desk/ticket/{$inquiry->ticket_code}/status", $payload);

        $response->assertRedirect(route('hospital.desk.show', $inquiry->ticket_code));

        $inquiry->refresh();
        $this->assertEquals('in_progress', $inquiry->status);
        $this->assertEquals('Patient called front desk on 10/07. Advised to visit cashier desk.', $inquiry->staff_notes);
    }
}