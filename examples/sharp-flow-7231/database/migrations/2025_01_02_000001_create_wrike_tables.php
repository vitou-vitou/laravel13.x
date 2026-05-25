<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('wrike_folders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon')->default('📁');
            $table->string('color')->default('bg-blue-500');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('wrike_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')->constrained('wrike_folders')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'completed', 'deferred', 'cancelled'])->default('active');
            $table->enum('importance', ['high', 'normal', 'low'])->default('normal');
            $table->string('assignee')->nullable();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->integer('effort')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('wrike_tasks');
        Schema::dropIfExists('wrike_folders');
    }
};
