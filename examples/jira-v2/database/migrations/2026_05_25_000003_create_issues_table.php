<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sprint_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['story', 'bug', 'task', 'epic'])->default('task');
            $table->enum('status', ['todo', 'in_progress', 'in_review', 'done'])->default('todo');
            $table->enum('priority', ['lowest', 'low', 'medium', 'high', 'highest'])->default('medium');
            $table->string('assignee')->nullable();
            $table->integer('story_points')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('issues');
    }
};