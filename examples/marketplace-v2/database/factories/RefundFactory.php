<?php

namespace Database\Factories;

use App\Enums\RefundStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Refund> */
class RefundFactory extends Factory
{
    protected $model = Refund::class;

    public function definition(): array
    {
        return [
            'payment_id' => Payment::factory(),
            'order_id' => Order::factory(),
            'initiated_by_user_id' => User::factory(),
            'amount_cents' => 1000,
            'status' => RefundStatus::Completed,
            'reason' => 'Customer request',
            'stripe_refund_id' => 're_fake_'.fake()->uuid(),
        ];
    }
}
