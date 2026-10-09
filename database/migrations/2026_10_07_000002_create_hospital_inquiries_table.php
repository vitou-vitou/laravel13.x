<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospital_inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_code', 32)->unique();
            $table->string('patient_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('category')->default('general');
            $table->string('urgency')->default('routine');
            $table->foreignId('department_id')->nullable()->constrained('hospital_departments')->nullOnDelete();
            $table->string('subject');
            $table->text('message');
            $table->string('status')->default('open');
            $table->text('staff_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('category');
            $table->index('urgency');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospital_inquiries');
    }
};
