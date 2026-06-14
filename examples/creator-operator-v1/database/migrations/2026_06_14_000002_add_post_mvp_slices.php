<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('operator_plan')->default('starter')->after('role');
        });

        Schema::create('weekly_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained()->cascadeOnDelete();
            $table->date('week_start');
            $table->unsignedInteger('videos_published')->default(0);
            $table->string('best_video_url')->nullable();
            $table->unsignedInteger('best_video_views')->nullable();
            $table->string('experiment')->nullable();
            $table->string('experiment_result')->nullable();
            $table->text('operator_notes')->nullable();
            $table->timestamps();

            $table->unique(['creator_id', 'week_start']);
        });

        Schema::create('monthly_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained()->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->string('platform')->default('youtube');
            $table->decimal('gross_payout_local', 12, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('payout_status')->default('estimated');
            $table->unsignedBigInteger('s_views')->default(0);
            $table->unsignedBigInteger('t_views')->default(0);
            $table->decimal('attributed_revenue', 12, 2)->default(0);
            $table->decimal('commission_rate_pct', 5, 2)->default(15);
            $table->decimal('monthly_ops_fee', 12, 2)->default(0);
            $table->decimal('commission_amount', 12, 2)->default(0);
            $table->decimal('creator_net', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('integration_webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('url');
            $table->string('secret')->nullable();
            $table->boolean('on_approved')->default(true);
            $table->boolean('on_published')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('integration_webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_webhook_id')->constrained()->cascadeOnDelete();
            $table->string('event');
            $table->json('payload');
            $table->unsignedSmallInteger('response_status')->nullable();
            $table->text('response_body')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_webhook_deliveries');
        Schema::dropIfExists('integration_webhooks');
        Schema::dropIfExists('monthly_settlements');
        Schema::dropIfExists('weekly_metrics');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('operator_plan');
        });
    }
};
