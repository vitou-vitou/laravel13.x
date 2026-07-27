<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('claim_id')->constrained('claims')->cascadeOnDelete();
            $table->enum('type', ['driver_license', 'vehicle_registration', 'police_report', 'repair_estimate', 'photo', 'other']);
            $table->string('original_filename');
            $table->string('storage_path', 500);
            $table->string('mime_type', 100);
            $table->unsignedInteger('size_bytes');
            $table->enum('status', ['pending', 'available', 'quarantined'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
