<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('initiated_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('amount_cents');
            $table->string('status')->default('pending');
            $table->string('reason');
            $table->string('stripe_refund_id')->nullable();
            $table->timestamps();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedInteger('refunded_cents')->default(0)->after('amount_cents');
        });

        Schema::table('payouts', function (Blueprint $table) {
            $table->string('stripe_transfer_id')->nullable()->after('amount_cents');
            $table->timestamp('scheduled_for')->nullable()->after('stripe_transfer_id');
            $table->timestamp('released_at')->nullable()->after('scheduled_for');
        });

        Schema::table('promo_codes', function (Blueprint $table) {
            $table->foreignId('vendor_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->unsignedInteger('min_subtotal_cents')->nullable()->after('value');
        });
    }

    public function down(): void
    {
        Schema::table('promo_codes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vendor_id');
            $table->dropColumn('min_subtotal_cents');
        });

        Schema::table('payouts', function (Blueprint $table) {
            $table->dropColumn(['stripe_transfer_id', 'scheduled_for', 'released_at']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('refunded_cents');
        });

        Schema::dropIfExists('refunds');
    }
};
