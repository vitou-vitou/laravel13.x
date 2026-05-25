<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('hive_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon')->default('🐝');
            $table->string('color')->default('bg-amber-500');
            $table->timestamps();
        });

        Schema::create('hive_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('hive_projects')->cascadeOnDelete();
            $table->string('title');
            $table->enum('status', ['to_do', 'in_progress', 'in_review', 'completed'])->default('to_do');
            $table->enum('priority', ['critical', 'high', 'medium', 'low'])->default('medium');
            $table->string('assignee')->nullable();
            $table->date('due_date')->nullable();
            $table->string('label')->nullable();
            $table->integer('time_logged')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('hive_actions');
        Schema::dropIfExists('hive_projects');
    }
};
