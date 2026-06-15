<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('publish_log_entries', function (Blueprint $table) {
            $table->string('tiktok_thumbnail_url', 1000)->nullable()->after('tiktok_url');
        });
    }

    public function down(): void
    {
        Schema::table('publish_log_entries', function (Blueprint $table) {
            $table->dropColumn('tiktok_thumbnail_url');
        });
    }
};
