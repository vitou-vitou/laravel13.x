<?php

namespace Tests\Feature;

use App\Filament\Resources\Pages\ListCompanyTasks;
use App\Filament\Resources\Pages\ListCustomerTasks;
use App\Filament\Resources\Pages\ListDailyIssueTasks;
use App\Models\CompanyTask;
use App\Models\CustomerTask;
use App\Models\DailyIssueTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CompanyTaskHubFilamentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_can_render_company_task_list_page(): void
    {
        $task = CompanyTask::factory()->create(['title' => 'Server Security Hardening']);

        Livewire::test(ListCompanyTasks::class)
            ->assertSuccessful()
            ->assertSee($task->title);
    }

    public function test_can_render_daily_issue_task_list_page(): void
    {
        $issue = DailyIssueTask::factory()->create(['title' => 'DB Deadlock under load']);

        Livewire::test(ListDailyIssueTasks::class)
            ->assertSuccessful()
            ->assertSee($issue->title);
    }

    public function test_can_render_customer_task_list_page(): void
    {
        $customerTask = CustomerTask::factory()->create([
            'title' => 'Onboard Beta Users',
            'customer_name' => 'Tesla Inc',
        ]);

        Livewire::test(ListCustomerTasks::class)
            ->assertSuccessful()
            ->assertSee($customerTask->title)
            ->assertSee('Tesla Inc');
    }
}
