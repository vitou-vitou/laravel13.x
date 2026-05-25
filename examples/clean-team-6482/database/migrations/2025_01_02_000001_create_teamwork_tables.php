<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tw_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon')->default('📁');
            $table->string('color')->default('bg-teal-500');
            $table->enum('status', ['active', 'on_hold', 'completed'])->default('active');
            $table->string('company')->nullable();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamps();
        });

        Schema::create('tw_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('tw_projects')->cascadeOnDelete();
            $table->string('title');
            $table->date('due_date');
            $table->boolean('completed')->default(false);
            $table->timestamps();
        });

        Schema::create('tw_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('tw_projects')->cascadeOnDelete();
            $table->foreignId('milestone_id')->nullable()->constrained('tw_milestones')->nullOnDelete();
            $table->string('title');
            $table->enum('status', ['new', 'in_progress', 'completed'])->default('new');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->string('assignee')->nullable();
            $table->date('due_date')->nullable();
            $table->integer('estimated_minutes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('tw_tasks');
        Schema::dropIfExists('tw_milestones');
        Schema::dropIfExists('tw_projects');
    }
};
