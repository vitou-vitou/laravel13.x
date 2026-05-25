<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('icon')->default('📄');
            $table->string('cover_color')->default('bg-gray-200');
            $table->enum('type', ['page', 'database'])->default('page');
            $table->longText('content')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('db_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->enum('status', ['not_started', 'in_progress', 'done', 'cancelled'])->default('not_started');
            $table->string('assignee')->nullable();
            $table->date('due_date')->nullable();
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->string('tags')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('db_rows');
        Schema::dropIfExists('pages');
    }
};
