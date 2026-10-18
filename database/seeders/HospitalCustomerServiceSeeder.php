<?php

namespace Database\Seeders;

use App\Models\HospitalDepartment;
use App\Models\HospitalFaq;
use App\Models\HospitalInquiry;
use App\Models\HospitalInquiryResponse;
use Illuminate\Database\Seeder;

class HospitalCustomerServiceSeeder extends Seeder
{
    public function run(): void
    {
        $emergency = HospitalDepartment::firstOrCreate(
            ['slug' => 'emergency-care'],
            [
                'name' => 'Emergency & Trauma Care',
                'code' => 'EMERG',
                'location' => 'Ground Floor, North Wing (Entrance A)',
                'phone' => '+1 (555) 019-9111',
                'email' => 'er-triage@stjudes-hospital.local',
                'operating_hours' => '24/7 Open 365 Days',
                'description' => 'Immediate critical and trauma medical care. Walk-in and ambulance bay.',
                'is_active' => true,
            ]
        );

        $outpatient = HospitalDepartment::firstOrCreate(
            ['slug' => 'outpatient-clinics'],
            [
                'name' => 'Outpatient Consultation & Specialist Clinics',
                'code' => 'OPD',
                'location' => 'Level 2, Tower B',
                'phone' => '+1 (555) 019-2001',
                'email' => 'outpatient@stjudes-hospital.local',
                'operating_hours' => 'Mon – Sat: 8:00 AM – 6:00 PM',
                'description' => 'Cardiology, Neurology, Pediatrics, Orthopedics, and General Internal Medicine.',
                'is_active' => true,
            ]
        );

        $billing = HospitalDepartment::firstOrCreate(
            ['slug' => 'billing-insurance'],
            [
                'name' => 'Patient Billing & Insurance Desk',
                'code' => 'BILL',
                'location' => 'Main Lobby, Desk 4-6',
                'phone' => '+1 (555) 019-3002',
                'email' => 'billing@stjudes-hospital.local',
                'operating_hours' => 'Mon – Fri: 7:30 AM – 6:00 PM | Sat: 8:00 AM – 1:00 PM',
                'description' => 'Invoice statements, health insurance pre-authorizations, financial counseling, and payment plans.',
                'is_active' => true,
            ]
        );

        $records = HospitalDepartment::firstOrCreate(
            ['slug' => 'medical-records'],
            [
                'name' => 'Health Records & Documentation',
                'code' => 'HIM',
                'location' => 'Lower Ground, Room LG-14',
                'phone' => '+1 (555) 019-4003',
                'email' => 'records@stjudes-hospital.local',
                'operating_hours' => 'Mon – Fri: 8:00 AM – 4:30 PM',
                'description' => 'Official copies of lab results, imaging scans, discharge summaries, and legal affidavits.',
                'is_active' => true,
            ]
        );

        $pharmacy = HospitalDepartment::firstOrCreate(
            ['slug' => 'pharmacy-diagnostic-lab'],
            [
                'name' => 'Central Pharmacy & Diagnostic Labs',
                'code' => 'PHARM',
                'location' => 'Ground Floor, South Corridor',
                'phone' => '+1 (555) 019-5004',
                'email' => 'pharmacy@stjudes-hospital.local',
                'operating_hours' => '24/7 Dispensing Service',
                'description' => 'Prescription fulfillment, medication consultations, and blood draw specimen collection.',
                'is_active' => true,
            ]
        );

        $faqs = [
            [
                'category' => 'Visiting Hours',
                'question' => 'What are the current general inpatient visiting hours?',
                'answer' => 'General ward visiting hours are 10:00 AM to 1:00 PM and 4:00 PM to 8:00 PM daily. Maximum 2 visitors at a time per patient bedside.',
                'display_order' => 1,
            ],
            [
                'category' => 'Billing & Insurance',
                'question' => 'Which health insurance and HMO networks are accepted?',
                'answer' => 'We accept Blue Cross Blue Shield, Aetna, Cigna, UnitedHealthcare, Medicare, and regional HMO providers. Contact the Billing Desk for policy coverage checks.',
                'display_order' => 2,
            ],
            [
                'category' => 'Appointments',
                'question' => 'How can I reschedule or cancel a specialist doctor appointment?',
                'answer' => 'You can submit a reschedule inquiry online using your ticket code or call the Outpatient Desk at +1 (555) 019-2001 at least 24 hours prior to your scheduled time.',
                'display_order' => 3,
            ],
            [
                'category' => 'Medical Records',
                'question' => 'How do I obtain copies of my lab tests or radiology scan reports?',
                'answer' => 'Submit an inquiry under Medical Records with your patient ID, or visit the Health Records counter in the Lower Ground wing with a valid government ID.',
                'display_order' => 4,
            ],
            [
                'category' => 'Emergency',
                'question' => 'When should I go directly to the Emergency Room versus Outpatient clinic?',
                'answer' => 'Seek immediate ER care for chest pain, severe shortness of breath, sudden numbness or speech difficulty, uncontrollable bleeding, or major trauma. For non-urgent symptoms, visit the Outpatient clinic.',
                'display_order' => 5,
            ],
        ];

        foreach ($faqs as $faq) {
            HospitalFaq::updateOrCreate(
                ['question' => $faq['question']],
                [
                    'category' => $faq['category'],
                    'answer' => $faq['answer'],
                    'display_order' => $faq['display_order'],
                    'is_published' => true,
                ]
            );
        }

        // Demo sample inquiries
        $demoTicket1 = HospitalInquiry::firstOrCreate(
            ['ticket_code' => 'HOSP-2026-DEMO1'],
            [
                'patient_name' => 'Sarah Connor',
                'email' => 'sarah.connor@example.com',
                'phone' => '+1 (555) 123-4567',
                'category' => 'billing',
                'urgency' => 'routine',
                'department_id' => $billing->id,
                'subject' => 'Explanation of bill item #8491 from last visit',
                'message' => 'Hello, I received a statement with a line item for diagnostic ultrasound that I thought was covered under my pre-authorized insurer plan. Could someone verify?',
                'status' => 'in_progress',
                'staff_notes' => 'Contacted insurance liaison on 10/06. Waiting for payer breakdown.',
            ]
        );

        HospitalInquiryResponse::firstOrCreate(
            [
                'inquiry_id' => $demoTicket1->id,
                'response_text' => 'Dear Ms. Connor, our billing team has received your query and submitted an itemized review with your insurance liaison. We will update you within 24 hours.',
            ],
            [
                'author_name' => 'Billing Representative',
                'is_internal' => false,
            ]
        );

        $demoTicket2 = HospitalInquiry::firstOrCreate(
            ['ticket_code' => 'HOSP-2026-DEMO2'],
            [
                'patient_name' => 'Robert Davis',
                'email' => 'robert.davis@example.com',
                'phone' => '+1 (555) 987-6543',
                'category' => 'appointment',
                'urgency' => 'urgent',
                'department_id' => $outpatient->id,
                'subject' => 'Request to advance cardiology follow-up visit',
                'message' => 'Dr. Chen asked me to see him in 2 weeks, but I am experiencing mild palpitations in the evening. Can I get an earlier appointment this week?',
                'status' => 'open',
            ]
        );
    }
}
