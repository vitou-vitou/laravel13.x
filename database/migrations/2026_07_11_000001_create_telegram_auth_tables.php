<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('plan')->default('free');
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('client_id', 32)->unique();
            $table->string('client_secret');
            $table->json('redirect_uris');
            $table->json('allowed_origins')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('telegram_bots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->string('bot_username');
            $table->text('bot_token');
            $table->json('domains')->nullable();
            $table->timestamps();
        });

        Schema::create('end_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('telegram_id')->unique();
            $table->string('username')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('photo_url')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
        });

        Schema::create('tenant_end_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('end_user_id')->constrained()->cascadeOnDelete();
            $table->string('external_user_id')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();

            $table->unique(['application_id', 'end_user_id']);
        });

        Schema::create('auth_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->string('state', 64)->unique();
            $table->string('code_challenge', 128);
            $table->string('code_challenge_method', 10)->default('S256');
            $table->string('redirect_uri');
            $table->string('flow', 20)->default('widget');
            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('auth_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auth_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('end_user_id')->constrained()->cascadeOnDelete();
            $table->string('code', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });

        Schema::create('auth_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->nullable()->constrained()->nullOnDelete();
            $table->string('flow', 20);
            $table->boolean('success');
            $table->string('failure_reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->unsignedBigInteger('telegram_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_audit_logs');
        Schema::dropIfExists('auth_codes');
        Schema::dropIfExists('auth_sessions');
        Schema::dropIfExists('tenant_end_users');
        Schema::dropIfExists('end_users');
        Schema::dropIfExists('telegram_bots');
        Schema::dropIfExists('applications');
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tenant_id');
        });
        Schema::dropIfExists('tenants');
    }
};
