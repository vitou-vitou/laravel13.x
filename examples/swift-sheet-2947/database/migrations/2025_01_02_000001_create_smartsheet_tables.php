<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ss_sheets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon')->default('📊');
            $table->string('color')->default('bg-emerald-500');
            $table->timestamps();
        });

        Schema::create('ss_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sheet_id')->constrained('ss_sheets')->cascadeOnDelete();
            $table->string('task_name');
            $table->string('assigned_to')->nullable();
            $table->enum('status', ['not_started', 'in_progress', 'complete', 'on_hold', 'blocked'])->default('not_started');
            $table->enum('priority', ['none', 'low', 'medium', 'high', 'critical'])->default('none');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('duration')->nullable();
            $table->integer('percent_complete')->default(0);
            $table->string('predecessors')->nullable();
            $table->string('comments')->nullable();
            $table->integer('budget')->nullable();
            $table->integer('actual_cost')->nullable();
            $table->integer('row_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('ss_rows');
        Schema::dropIfExists('ss_sheets');
    }
};
