<?php

namespace Tests\Feature;

use App\Models\HospitalDepartment;
use App\Models\HospitalFaq;
use App\Models\HospitalInquiry;
use App\Models\HospitalInquiryResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HospitalInquiryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        HospitalDepartment::create([
            'slug' => 'emergency-care',
            'name' => 'Emergency Care',
            'code' => 'ER',
            'location' => 'Ground Floor',
            'phone' => '+1 (555) 019-9111',
            'operating_hours' => '24/7',
            'is_active' => true,
        ]);

        HospitalFaq::create([
            'category' => 'Visiting',
            'question' => 'What are visiting hours?',
            'answer' => 'Visiting hours are 10am to 8pm daily.',
            'display_order' => 1,
            'is_published' => true,
        ]);
    }

    public function test_hospital_home_page_loads_successfully(): void
    {
        $response = $this->get('/hospital');

        $response->assertOk();
        $response->assertSee('CareDesk');
        $response->assertSee('Submit New Inquiry');
        $response->assertSee('Track Ticket');
    }

    public function test_inquiry_create_page_renders_form(): void
    {
        $response = $this->get('/hospital/inquiry/new');

        $response->assertOk();
        $response->assertSee('Submit a Patient Inquiry');
        $response->assertSee('Emergency Care');
    }

    public function test_patient_can_submit_inquiry_with_valid_data(): void
    {
        $dept = HospitalDepartment::first();

        $payload = [
            'patient_name' => 'Jane Smith',
            'email' => 'jane.smith@example.com',
            'phone' => '+1 (555) 444-5555',
            'category' => 'billing',
            'urgency' => 'routine',
            'department_id' => $dept->id,
            'subject' => 'Need explanation on co-pay charges',
            'message' => 'Please explain why my invoice includes a 50 dollar co-pay for consultation.',
        ];

        $response = $this->post('/hospital/inquiry', $payload);

        $this->assertDatabaseHas('hospital_inquiries', [
            'patient_name' => 'Jane Smith',
            'email' => 'jane.smith@example.com',
            'category' => 'billing',
            'status' => 'open',
        ]);

        $inquiry = HospitalInquiry::where('email', 'jane.smith@example.com')->first();
        $this->assertNotNull($inquiry);
        $this->assertStringStartsWith('HOSP-', $inquiry->ticket_code);

        $response->assertRedirect(route('hospital.track', ['code' => $inquiry->ticket_code]));
    }

    public function test_inquiry_submission_requires_contact_method(): void
    {
        $payload = [
            'patient_name' => 'Anonymous Patient',
            'email' => '',
            'phone' => '',
            'category' => 'general',
            'urgency' => 'routine',
            'subject' => 'Test subject line',
            'message' => 'This is a test message that has enough characters.',
        ];

        $response = $this->from('/hospital/inquiry/new')->post('/hospital/inquiry', $payload);

        $response->assertRedirect('/hospital/inquiry/new');
        $response->assertSessionHasErrors('contact');
    }

    public function test_patient_can_track_ticket_and_only_see_public_responses(): void
    {
        $inquiry = HospitalInquiry::create([
            'ticket_code' => 'HOSP-2026-TEST1',
            'patient_name' => 'Michael Corleone',
            'email' => 'michael@example.com',
            'phone' => '+1 (555) 111-2222',
            'category' => 'appointment',
            'urgency' => 'urgent',
            'subject' => 'Need urgent cardiology appointment',
            'message' => 'Experiencing high heart rate, need schedule advance.',
            'status' => 'in_progress',
        ]);

        HospitalInquiryResponse::create([
            'inquiry_id' => $inquiry->id,
            'author_name' => 'Dr. Wilson',
            'response_text' => 'We have reserved a slot for you tomorrow at 9:00 AM.',
            'is_internal' => false,
        ]);

        HospitalInquiryResponse::create([
            'inquiry_id' => $inquiry->id,
            'author_name' => 'Desk Agent',
            'response_text' => 'CONFIDENTIAL: Escalated directly to Chief of Cardiology.',
            'is_internal' => true,
        ]);

        $response = $this->get('/hospital/track?code=HOSP-2026-TEST1');

        $response->assertOk();
        $response->assertSee('HOSP-2026-TEST1');
        $response->assertSee('Michael Corleone');
        $response->assertSee('We have reserved a slot for you tomorrow at 9:00 AM.');
        $response->assertDontSee('CONFIDENTIAL: Escalated directly to Chief of Cardiology.');
    }

    public function test_department_directory_and_faq_pages_render(): void
    {
        $deptResponse = $this->get('/hospital/departments');
        $deptResponse->assertOk();
        $deptResponse->assertSee('Emergency Care');

        $faqResponse = $this->get('/hospital/faq');
        $faqResponse->assertOk();
        $faqResponse->assertSee('What are visiting hours?');
    }
}