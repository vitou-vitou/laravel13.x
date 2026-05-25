<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('jira_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key', 10);
            $table->string('icon')->default('📋');
            $table->string('type')->default('scrum');
            $table->timestamps();
        });

        Schema::create('jira_sprints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('jira_projects')->cascadeOnDelete();
            $table->string('name');
            $table->enum('status', ['planning', 'active', 'completed'])->default('planning');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });

        Schema::create('jira_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('jira_projects')->cascadeOnDelete();
            $table->foreignId('sprint_id')->nullable()->constrained('jira_sprints')->nullOnDelete();
            $table->string('key');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['story', 'bug', 'task', 'epic'])->default('task');
            $table->enum('status', ['todo', 'in_progress', 'in_review', 'done'])->default('todo');
            $table->enum('priority', ['lowest', 'low', 'medium', 'high', 'highest'])->default('medium');
            $table->string('assignee')->nullable();
            $table->string('reporter')->nullable();
            $table->integer('story_points')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('jira_issues');
        Schema::dropIfExists('jira_sprints');
        Schema::dropIfExists('jira_projects');
    }
};
