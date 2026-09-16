<?php

namespace Tests\Feature;

use App\Enums\IssueSeverity;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\CompanyTask;
use App\Models\CustomerTask;
use App\Models\DailyIssueTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyTaskHubDomainTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_and_manage_company_task(): void
    {
        $user = User::factory()->create();

        $task = CompanyTask::create([
            'title' => 'Quarterly Financial Audit',
            'description' => 'Prepare documents for Q3 audit',
            'department' => 'Finance',
            'priority' => TaskPriority::High,
            'status' => TaskStatus::InProgress,
            'assignee_id' => $user->id,
            'due_date' => now()->addDays(7),
        ]);

        $this->assertDatabaseHas('company_tasks', [
            'id' => $task->id,
            'title' => 'Quarterly Financial Audit',
            'priority' => 'high',
            'status' => 'in_progress',
        ]);
        $this->assertEquals(TaskPriority::High, $task->priority);
        $this->assertEquals(TaskStatus::InProgress, $task->status);
        $this->assertEquals($user->id, $task->assignee->id);
    }

    public function test_can_create_and_resolve_daily_issue_task(): void
    {
        $resolver = User::factory()->create();

        $issue = DailyIssueTask::create([
            'title' => 'Payment Gateway Timeout on Checkout',
            'incident_type' => 'bug',
            'severity' => IssueSeverity::Critical,
            'status' => TaskStatus::Pending,
        ]);

        $this->assertEquals(IssueSeverity::Critical, $issue->severity);
        $this->assertNull($issue->resolved_at);

        $issue->markResolved(
            notes: 'Increased curl timeout from 2s to 10s',
            rootCause: 'Upstream gateway DNS latency',
            resolverId: $resolver->id
        );

        $issue->refresh();
        $this->assertEquals(TaskStatus::Completed, $issue->status);
        $this->assertNotNull($issue->resolved_at);
        $this->assertEquals('Upstream gateway DNS latency', $issue->root_cause);
        $this->assertEquals($resolver->id, $issue->resolver->id);
    }

    public function test_can_create_and_track_customer_task(): void
    {
        $handler = User::factory()->create();

        $customerTask = CustomerTask::create([
            'title' => 'Enterprise SLA Onboarding Review',
            'customer_name' => 'Acme Corp',
            'customer_email' => 'tech@acme.com',
            'task_type' => 'onboarding',
            'priority' => TaskPriority::Urgent,
            'status' => TaskStatus::Pending,
            'handler_id' => $handler->id,
            'due_date' => now()->addDays(2),
        ]);

        $this->assertDatabaseHas('customer_tasks', [
            'id' => $customerTask->id,
            'customer_name' => 'Acme Corp',
            'customer_email' => 'tech@acme.com',
            'priority' => 'urgent',
        ]);
        $this->assertEquals(TaskPriority::Urgent, $customerTask->priority);
        $this->assertEquals($handler->id, $customerTask->handler->id);
    }
}
