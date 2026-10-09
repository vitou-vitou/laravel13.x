<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospital_inquiry_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inquiry_id')->constrained('hospital_inquiries')->cascadeOnDelete();
            $table->string('author_name')->default('Patient Support Team');
            $table->text('response_text');
            $table->boolean('is_internal')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospital_inquiry_responses');
    }
};
