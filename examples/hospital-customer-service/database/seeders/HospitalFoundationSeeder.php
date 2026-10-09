<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Faq;
use Illuminate\Database\Seeder;

class HospitalFoundationSeeder extends Seeder
{
    public function run(): void
    {
        $opd = Department::create([
            'name' => 'General Outpatient Clinic',
            'code' => 'OPD',
            'description' => 'Primary consultations, physical exams, and general practitioner care.',
            'counter_location' => 'Building A, Floor 1, Desk 101',
            'operating_hours' => 'Mon-Sat: 07:30 - 17:00',
            'phone' => '+855 23 888 101',
            'is_active' => true,
        ]);

        $lab = Department::create([
            'name' => 'Clinical Pathology & Laboratory',
            'code' => 'LAB',
            'description' => 'Blood draws, specimen intake, and diagnostic report collections.',
            'counter_location' => 'Building B, Floor 1, Counter 202',
            'operating_hours' => 'Mon-Sun: 24 Hours',
            'phone' => '+855 23 888 202',
            'is_active' => true,
        ]);

        $cardio = Department::create([
            'name' => 'Cardiology & Heart Center',
            'code' => 'CARDIO',
            'description' => 'Specialized cardiac diagnostics, ECG, and echocardiography.',
            'counter_location' => 'Building C, Floor 2, Counter 305',
            'operating_hours' => 'Mon-Fri: 08:00 - 16:30',
            'phone' => '+855 23 888 305',
            'is_active' => true,
        ]);

        Department::create([
            'name' => 'Emergency & Acute Care',
            'code' => 'EMERG',
            'description' => '24/7 critical triage and rapid resuscitation bay.',
            'counter_location' => 'Ground Floor West Wing, ER Entrance',
            'operating_hours' => '24/7 Immediate Care',
            'phone' => '+855 23 888 911',
            'is_active' => true,
        ]);

        Faq::create([
            'category' => 'Visiting Hours & Policies',
            'question' => 'What are the general inpatient visiting hours?',
            'answer' => 'General ward visiting hours are daily from 10:00 AM to 12:00 PM and 4:00 PM to 8:00 PM. Maximum 2 visitors per patient at any given time.',
            'action_type' => 'direct_answer',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Faq::create([
            'category' => 'Visiting Hours & Policies',
            'question' => 'Are children permitted in inpatient intensive care units?',
            'answer' => 'Visitors under 12 years are restricted from ICU and infectious wards to protect patient health and prevent infections.',
            'action_type' => 'direct_answer',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        Faq::create([
            'category' => 'Diagnostic & Test Prep',
            'question' => 'How many hours must I fast before a fasting blood glucose or lipid panel?',
            'answer' => 'Patients must fast for 8 to 12 hours prior to blood collection. Water is permitted, but avoid coffee, tea, juices, and smoking.',
            'action_type' => 'route_department',
            'target_department_id' => $lab->id,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Faq::create([
            'category' => 'Diagnostic & Test Prep',
            'question' => 'Where do I collect laboratory paper test results?',
            'answer' => 'Routine lab test results can be collected at Building B, Floor 1, Counter 202 using your diagnostic slip reference.',
            'action_type' => 'route_department',
            'target_department_id' => $lab->id,
            'sort_order' => 2,
            'is_active' => true,
        ]);

        Faq::create([
            'category' => 'Registration & Queue',
            'question' => 'What documents do I need to register as a first-time patient?',
            'answer' => 'Please bring a valid national identification card or passport and your insurance policy card if filing third-party claims.',
            'action_type' => 'route_department',
            'target_department_id' => $opd->id,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Faq::create([
            'category' => 'Emergency vs Outpatient',
            'question' => 'When should I go to the Emergency Room instead of the Outpatient Clinic?',
            'answer' => 'Severe chest pain, sudden difficulty breathing, uncontrollable bleeding, or severe head trauma require immediate evaluation at the ER Ground Floor West Wing.',
            'action_type' => 'direct_answer',
            'sort_order' => 1,
            'is_active' => true,
        ]);
    }
}
