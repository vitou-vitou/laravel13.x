<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username', 100)->nullable();
            $table->string('email', 150)->unique();
            $table->string('password');
            $table->rememberToken();
            $table->string('country', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('device_type', 20)->nullable();
            $table->string('signup_source', 20)->nullable();
            $table->string('avatar', 255)->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
