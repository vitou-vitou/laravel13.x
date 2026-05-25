<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('spaces', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon')->default('📁');
            $table->timestamps();
        });
        Schema::create('cu_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('space_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('assignee')->nullable();
            $table->string('status')->default('open');
            $table->string('priority')->default('normal');
            $table->string('tag')->nullable();
            $table->date('due_date')->nullable();
            $table->integer('time_estimate')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('cu_tasks');
        Schema::dropIfExists('spaces');
    }
};
