<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('mon_boards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon')->default('📋');
            $table->string('color')->default('bg-indigo-500');
            $table->timestamps();
        });

        Schema::create('mon_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained('mon_boards')->cascadeOnDelete();
            $table->string('name');
            $table->string('color')->default('bg-green-500');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('mon_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('mon_groups')->cascadeOnDelete();
            $table->foreignId('board_id')->constrained('mon_boards')->cascadeOnDelete();
            $table->string('title');
            $table->enum('status', ['working_on_it', 'done', 'stuck', 'not_started'])->default('not_started');
            $table->string('assignee')->nullable();
            $table->date('due_date')->nullable();
            $table->enum('priority', ['critical', 'high', 'medium', 'low'])->default('medium');
            $table->string('timeline_start')->nullable();
            $table->string('timeline_end')->nullable();
            $table->integer('progress')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('mon_items');
        Schema::dropIfExists('mon_groups');
        Schema::dropIfExists('mon_boards');
    }
};
