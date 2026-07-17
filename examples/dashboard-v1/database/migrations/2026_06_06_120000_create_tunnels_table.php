<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tunnels', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('domain');
            $table->string('herd_host');
            $table->boolean('is_active')->default(false);
            $table->timestamp('last_verified_at')->nullable();
            $table->string('last_verified_status')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tunnels');
    }
};
