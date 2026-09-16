<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('department')->default('Operations');
            $table->string('priority')->default('medium');
            $table->string('status')->default('pending');
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->timestamps();
        });

        Schema::create('daily_issue_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('incident_type')->default('bug');
            $table->string('severity')->default('major');
            $table->string('status')->default('open');
            $table->text('root_cause')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->foreignId('resolver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('customer_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('task_type')->default('onboarding');
            $table->string('priority')->default('medium');
            $table->string('status')->default('open');
            $table->text('notes')->nullable();
            $table->foreignId('handler_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_tasks');
        Schema::dropIfExists('daily_issue_tasks');
        Schema::dropIfExists('company_tasks');
    }
};
