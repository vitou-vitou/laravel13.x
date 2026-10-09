<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_requests', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_code')->unique();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->string('patient_name');
            $table->string('patient_phone');
            $table->string('patient_email')->nullable();
            $table->string('preferred_doctor')->nullable();
            $table->date('preferred_date');
            $table->string('preferred_time_slot');
            $table->string('status')->default('pending');
            $table->date('confirmed_date')->nullable();
            $table->string('confirmed_time_slot')->nullable();
            $table->string('assigned_room')->nullable();
            $table->text('staff_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_requests');
    }
};
