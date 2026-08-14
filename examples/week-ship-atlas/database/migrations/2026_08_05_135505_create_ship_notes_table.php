<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ship_notes', function (Blueprint $table) {
            $table->id();
            $table->string('weekday', 16);
            $table->string('title');
            $table->string('region');
            $table->string('company_habit');
            $table->string('project_type');
            $table->text('practice')->nullable();
            $table->string('verdict', 16)->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ship_notes');
    }
};
