<?php

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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('confirmed');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->char('currency', 3)->default('USD');
            $table->string('payment_method');
            $table->string('payment_terms')->nullable();
            $table->date('due_date')->nullable();
            $table->string('shipping_method');
            $table->json('shipping_address')->nullable();
            $table->date('estimated_delivery')->nullable();
            $table->string('priority')->default('normal');
            $table->text('internal_notes')->nullable();
            $table->text('customer_notes')->nullable();
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
