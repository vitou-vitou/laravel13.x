<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('customer')->after('password');
        });

        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('default_commission_bps')->default(1000);
            $table->timestamps();
        });

        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('store_name');
            $table->string('slug')->unique();
            $table->string('status')->default('pending');
            $table->text('stripe_account_id')->nullable();
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->unsignedInteger('rating_count')->default(0);
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('status')->default('draft');
            $table->string('image_path')->nullable();
            $table->timestamps();

            $table->unique(['vendor_id', 'slug']);
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku');
            $table->string('name')->default('Default');
            $table->unsignedInteger('price_cents');
            $table->unsignedInteger('stock_qty')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['product_id', 'sku']);
        });

        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('session_id')->nullable()->index();
            $table->string('status')->default('active');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('cart_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained();
            $table->unsignedInteger('quantity');
            $table->timestamps();

            $table->unique(['cart_id', 'product_variant_id']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending_payment');
            $table->unsignedInteger('total_cents');
            $table->string('stripe_checkout_session_id')->nullable()->unique();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained();
            $table->string('status')->default('pending');
            $table->unsignedInteger('commission_bps');
            $table->unsignedInteger('subtotal_cents');
            $table->string('tracking_number')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained();
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('unit_price_cents');
            $table->string('product_name_snapshot');
            $table->string('variant_name_snapshot');
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->unsignedInteger('amount_cents');
            $table->string('stripe_payment_intent_id')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->string('note')->nullable();
            $table->timestamps();
        });

        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_group_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->unsignedInteger('amount_cents');
            $table->timestamps();
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('body')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'user_id', 'order_id']);
        });

        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('opened');
            $table->text('reason');
            $table->timestamps();
        });

        Schema::create('dispute_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispute_messages');
        Schema::dropIfExists('disputes');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('payouts');
        Schema::dropIfExists('payment_audit_logs');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_lines');
        Schema::dropIfExists('order_groups');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('cart_lines');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('vendors');
        Schema::dropIfExists('platform_settings');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
