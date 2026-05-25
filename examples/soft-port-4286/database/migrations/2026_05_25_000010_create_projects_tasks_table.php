<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->default('#6366f1');
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('assignee')->nullable();
            $table->string('status')->default('todo');
            $table->string('priority')->default('medium');
            $table->date('due_date')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('projects');
    }
};
