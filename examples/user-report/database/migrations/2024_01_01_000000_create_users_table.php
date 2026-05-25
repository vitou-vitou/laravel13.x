<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken(); // string, nullable, 100
            $table->string('country', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->enum('device_type', ['mobile', 'desktop', 'tablet'])->nullable();
            $table->string('signup_source', 100)->nullable()->comment('e.g. google, facebook, direct');
            $table->string('avatar')->nullable()->comment('URL or file path');
            $table->decimal('geo_lat', 10, 7)->nullable();
            $table->decimal('geo_long', 10, 7)->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();

            // Indexes for query performance
            $table->index('country');
            $table->index('city');
            $table->index('device_type');
            $table->index('signup_source');
            $table->index('last_login_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
