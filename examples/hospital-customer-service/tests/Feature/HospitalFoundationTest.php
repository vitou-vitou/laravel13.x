<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Faq;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HospitalFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_guest_can_view_hospital_portal_with_disclaimer_and_departments(): void
    {
        $dept = Department::create([
            'name' => 'Pediatrics Clinic',
            'code' => 'PEDIAT',
            'counter_location' => 'Building A, Floor 2',
            'operating_hours' => 'Mon-Fri: 08:00 - 17:00',
            'is_active' => true,
        ]);

        Faq::create([
            'category' => 'Visiting Hours',
            'question' => 'Can parents stay overnight in pediatric ward?',
            'answer' => 'One parent or legal guardian may accompany a pediatric inpatient overnight.',
            'target_department_id' => $dept->id,
            'is_active' => true,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Hospital Customer Care');
        $response->assertSee('non-clinical customer service');
        $response->assertSee('Pediatrics Clinic');
        $response->assertSee('Building A, Floor 2');
        $response->assertSee('Can parents stay overnight in pediatric ward?');
    }

    public function test_api_returns_only_active_departments(): void
    {
        Department::create([
            'name' => 'General Medicine',
            'code' => 'GEN',
            'counter_location' => 'Desk 1',
            'operating_hours' => '8AM - 5PM',
            'is_active' => true,
        ]);

        Department::create([
            'name' => 'Archived Clinic',
            'code' => 'OLD',
            'counter_location' => 'Basement',
            'operating_hours' => 'Closed',
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/v1/departments');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.code', 'GEN');
    }

    public function test_api_returns_faq_tree_grouped_by_category(): void
    {
        Faq::create([
            'category' => 'Preparation',
            'question' => 'How to prepare for ultrasound?',
            'answer' => 'Drink 1 liter of water 1 hour prior.',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Faq::create([
            'category' => 'Visiting',
            'question' => 'What are visiting hours?',
            'answer' => '10 AM to 8 PM.',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/faq/tree');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                'Preparation',
                'Visiting',
            ],
        ]);
    }

    public function test_api_searches_faq_by_keyword(): void
    {
        Faq::create([
            'category' => 'Lab',
            'question' => 'Fasting duration for glucose test?',
            'answer' => 'Fast for 10 hours.',
            'is_active' => true,
        ]);

        Faq::create([
            'category' => 'Billing',
            'question' => 'Accepted insurance cards?',
            'answer' => 'We accept all major national health policies.',
            'is_active' => true,
        ]);

        $searchResponse = $this->getJson('/api/v1/faq/search?q=glucose');

        $searchResponse->assertStatus(200);
        $searchResponse->assertJsonCount(1, 'data');
        $searchResponse->assertJsonPath('data.0.question', 'Fasting duration for glucose test?');
    }

    public function test_admin_can_create_and_update_department(): void
    {
        $createResponse = $this->postJson('/api/v1/admin/departments', [
            'name' => 'Orthopedics Center',
            'code' => 'ORTHO',
            'description' => 'Bone and joint specialists',
            'counter_location' => 'Building D, Floor 3',
            'operating_hours' => 'Mon-Sat 08:00 - 16:00',
            'phone' => '+855 23 888 404',
            'is_active' => true,
        ]);

        $createResponse->assertStatus(201);
        $deptId = $createResponse->json('data.id');

        $this->assertDatabaseHas('departments', [
            'id' => $deptId,
            'code' => 'ORTHO',
        ]);

        $updateResponse = $this->putJson("/api/v1/admin/departments/{$deptId}", [
            'operating_hours' => 'Mon-Sat 08:00 - 18:00',
        ]);

        $updateResponse->assertStatus(200);
        $this->assertDatabaseHas('departments', [
            'id' => $deptId,
            'operating_hours' => 'Mon-Sat 08:00 - 18:00',
        ]);
    }

    public function test_admin_can_manage_faq_lifecycle(): void
    {
        $dept = Department::create([
            'name' => 'Radiology',
            'code' => 'RAD',
            'counter_location' => 'Ground Floor',
            'operating_hours' => '24/7',
            'is_active' => true,
        ]);

        $createResponse = $this->postJson('/api/v1/admin/faqs', [
            'category' => 'Radiology Guidance',
            'question' => 'Can pregnant patients undergo X-Ray?',
            'answer' => 'Pregnant patients must notify radiological staff prior to scan.',
            'target_department_id' => $dept->id,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $createResponse->assertStatus(201);
        $faqId = $createResponse->json('data.id');

        $updateResponse = $this->putJson("/api/v1/admin/faqs/{$faqId}", [
            'answer' => 'Always inform radiology staff of suspected or confirmed pregnancy.',
        ]);

        $updateResponse->assertStatus(200);
        $this->assertDatabaseHas('faqs', [
            'id' => $faqId,
            'answer' => 'Always inform radiology staff of suspected or confirmed pregnancy.',
        ]);

        $deleteResponse = $this->deleteJson("/api/v1/admin/faqs/{$faqId}");
        $deleteResponse->assertStatus(200);
        $this->assertDatabaseMissing('faqs', [
            'id' => $faqId,
        ]);
    }
}
