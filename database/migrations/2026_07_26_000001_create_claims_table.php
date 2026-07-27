<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claims', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('policy_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('policyholder_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('reference')->nullable()->unique();
            $table->string('incident_type')->nullable();
            $table->dateTime('incident_at')->nullable();
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'policyholder_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};
