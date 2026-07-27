<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('ref', 30)->unique();
            $table->foreignId('claimant_id')->constrained('users');
            $table->foreignId('adjuster_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['collision', 'theft', 'injury']);
            $table->enum('status', ['submitted', 'under_review', 'info_requested', 'approved', 'rejected', 'paid'])->default('submitted');
            $table->timestamp('incident_at');
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->text('address')->nullable();
            $table->text('description');
            $table->date('estimated_resolution_date')->nullable();
            $table->json('missing_docs')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'adjuster_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};
