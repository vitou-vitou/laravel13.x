<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key', 10);
            $table->string('icon')->default('📋');
            $table->enum('type', ['scrum', 'kanban'])->default('scrum');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('projects');
    }
};