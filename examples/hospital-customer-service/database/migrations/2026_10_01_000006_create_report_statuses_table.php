<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('phone_last_four', 4);
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->string('test_category');
            $table->string('status')->default('in_analysis');
            $table->string('pickup_counter')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('collected_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_statuses');
    }
};
