<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('sso_provider')->nullable()->after('password');
            $table->string('sso_id')->nullable()->after('sso_provider');
            $table->unique(['sso_provider', 'sso_id']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['sso_provider', 'sso_id']);
            $table->dropColumn(['sso_provider', 'sso_id']);
        });
    }
};
