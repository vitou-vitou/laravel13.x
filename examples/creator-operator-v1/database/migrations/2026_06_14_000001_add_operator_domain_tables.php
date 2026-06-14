<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('creator')->after('email');
        });

        Schema::create('creators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('handle')->unique();
            $table->string('tiktok_url');
            $table->string('tier')->default('lite');
            $table->string('music_policy')->default('skip');
            $table->string('youtube_manager_email')->nullable();
            $table->string('meta_manager_email')->nullable();
            $table->date('last_run_date')->nullable();
            $table->text('onboarding_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('publish_log_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained()->cascadeOnDelete();
            $table->date('logged_on');
            $table->string('tiktok_url');
            $table->string('yt_url')->nullable();
            $table->string('ig_url')->nullable();
            $table->string('yt_video_id')->nullable();
            $table->string('title_variant')->nullable();
            $table->timestamp('posted_time')->nullable();
            $table->string('status')->default('pending_approval');
            $table->unsignedInteger('views_yt_7d')->nullable();
            $table->unsignedInteger('views_ig_7d')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['creator_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publish_log_entries');
        Schema::dropIfExists('creators');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
