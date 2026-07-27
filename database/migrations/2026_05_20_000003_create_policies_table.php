<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('policyholder_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('policy_number');
            $table->string('vehicle_make')->nullable();
            $table->string('vehicle_model')->nullable();
            $table->unsignedSmallInteger('vehicle_year')->nullable();
            $table->string('vehicle_plate')->nullable();
            $table->unsignedBigInteger('coverage_amount_minor')->default(0);
            $table->string('currency', 3)->default('KHR');
            $table->date('active_from');
            $table->date('active_to');
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['tenant_id', 'policy_number']);
            $table->index(['tenant_id', 'policyholder_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policies');
    }
};
