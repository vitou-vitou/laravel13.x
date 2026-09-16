<?php

namespace Database\Seeders;

use App\Enums\IssueSeverity;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\CompanyTask;
use App\Models\CustomerTask;
use App\Models\DailyIssueTask;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@companytaskhub.test'],
            [
                'name' => 'Operations Admin',
                'password' => Hash::make('password'),
            ]
        );

        $techLead = User::firstOrCreate(
            ['email' => 'lead@companytaskhub.test'],
            [
                'name' => 'Tech Lead',
                'password' => Hash::make('password'),
            ]
        );

        // 1. Company General Tasks
        CompanyTask::create([
            'title' => 'Complete Annual Compliance & IT Security Review',
            'description' => 'Review all server credentials, IAM access roles and SSL certificates.',
            'department' => 'IT & Security',
            'priority' => TaskPriority::High,
            'status' => TaskStatus::InProgress,
            'assignee_id' => $admin->id,
            'due_date' => now()->addDays(10),
        ]);

        CompanyTask::create([
            'title' => 'Q4 Budget & Operations Planning',
            'description' => 'Finalize team expansion headcount and cloud infrastructure estimates.',
            'department' => 'Operations',
            'priority' => TaskPriority::Urgent,
            'status' => TaskStatus::Pending,
            'assignee_id' => $admin->id,
            'due_date' => now()->addDays(5),
        ]);

        // 2. Daily Issue Tasks
        DailyIssueTask::create([
            'title' => 'Stripe Webhook Intermittent 504 Gateway Timeout',
            'incident_type' => 'outage',
            'severity' => IssueSeverity::Critical,
            'status' => TaskStatus::Pending,
            'resolver_id' => $techLead->id,
        ]);

        $resolvedIssue = DailyIssueTask::create([
            'title' => 'Memory spike on monthly invoice PDF generation',
            'incident_type' => 'performance',
            'severity' => IssueSeverity::Major,
            'status' => TaskStatus::Pending,
            'resolver_id' => $techLead->id,
        ]);
        $resolvedIssue->markResolved(
            notes: 'Switched chunk size to 250 records per batch in generator queue job.',
            rootCause: 'Unbuffered Eloquent collection hydrate.',
            resolverId: $techLead->id
        );

        // 3. Customer Tasks
        CustomerTask::create([
            'title' => 'Acme Corp SSO SAML2 Configuration & Handshake',
            'customer_name' => 'Acme Corporation',
            'customer_email' => 'secops@acme.com',
            'task_type' => 'onboarding',
            'priority' => TaskPriority::High,
            'status' => TaskStatus::InProgress,
            'handler_id' => $admin->id,
            'due_date' => now()->addDays(3),
            'notes' => 'Awaiting XML metadata file from Acme IT department.',
        ]);

        CustomerTask::create([
            'title' => 'Global Logistics API Rate Limit Increase',
            'customer_name' => 'Global Logistics Co.',
            'customer_email' => 'api-admin@globallogistics.com',
            'task_type' => 'feature_request',
            'priority' => TaskPriority::Medium,
            'status' => TaskStatus::Pending,
            'handler_id' => $techLead->id,
            'due_date' => now()->addDays(7),
            'notes' => 'Customer requested 5,000 req/min tier for holiday season.',
        ]);
    }
}
