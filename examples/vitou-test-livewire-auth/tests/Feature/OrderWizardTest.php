<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    actingAs(User::factory()->create());
});

test('order wizard page loads for authenticated user', function () {
    $this->get(route('orders.create'))->assertOk();
});

test('order wizard renders all 6 step labels', function () {
    Livewire::test('pages::orders.create')
        ->assertSee('Customer')
        ->assertSee('Items')
        ->assertSee('Shipping')
        ->assertSee('Payment')
        ->assertSee('Notes')
        ->assertSee('Review');
});

test('step 1 starts as current step', function () {
    Livewire::test('pages::orders.create')
        ->assertSet('currentStep', 1);
});

test('next step advances step counter', function () {
    $customer = Customer::factory()->create();

    Livewire::test('pages::orders.create')
        ->set('customerId', $customer->id)
        ->call('nextStep')
        ->assertSet('currentStep', 2);
});

test('previous step goes back', function () {
    $customer = Customer::factory()->create();

    Livewire::test('pages::orders.create')
        ->set('customerId', $customer->id)
        ->call('nextStep')
        ->assertSet('currentStep', 2)
        ->call('previousStep')
        ->assertSet('currentStep', 1);
});

test('step 1 fails validation without customer', function () {
    Livewire::test('pages::orders.create')
        ->set('customerId', null)
        ->set('isNewCustomer', false)
        ->call('nextStep')
        ->assertHasErrors(['customerId' => 'required'])
        ->assertSet('currentStep', 1);
});

test('step 1 passes with existing customer selected', function () {
    $customer = Customer::factory()->create();

    Livewire::test('pages::orders.create')
        ->set('customerId', $customer->id)
        ->call('nextStep')
        ->assertHasNoErrors(['customerId'])
        ->assertSet('currentStep', 2);
});

test('step 2 fails without items', function () {
    $customer = Customer::factory()->create();

    Livewire::test('pages::orders.create')
        ->set('customerId', $customer->id)
        ->set('currentStep', 2)
        ->set('items', [])
        ->call('nextStep')
        ->assertHasErrors(['items'])
        ->assertSet('currentStep', 2);
    
});

test('step 2 fails when item missing product', function () {
    $customer = Customer::factory()->create();

    Livewire::test('pages::orders.create')
        ->set('customerId', $customer->id)
        ->set('currentStep', 2)
        ->set('items', [
            ['product_id' => null, 'quantity' => 1, 'unit_price' => 10, 'discount' => 0],
        ])
        ->call('nextStep')
        ->assertHasErrors(['items.0.product_id' => 'required']);
});

test('step 3 fails without shipping method', function () {
    Livewire::test('pages::orders.create')
        ->set('currentStep', 3)
        ->set('shippingMethod', '')
        ->call('nextStep')
        ->assertHasErrors(['shippingMethod' => 'required'])
        ->assertSet('currentStep', 3);
});

test('step 4 fails without payment method', function () {
    Livewire::test('pages::orders.create')
        ->set('currentStep', 4)
        ->set('paymentMethod', '')
        ->call('nextStep')
        ->assertHasErrors(['paymentMethod' => 'required'])
        ->assertSet('currentStep', 4);
});

test('invoice payment requires payment terms', function () {
    Livewire::test('pages::orders.create')
        ->set('currentStep', 4)
        ->set('paymentMethod', 'invoice')
        ->set('paymentTerms', '')
        ->set('dueDate', '')
        ->call('nextStep')
        ->assertHasErrors(['paymentTerms', 'dueDate']);
});

test('step 6 submit fails without confirm', function () {
    $customer = Customer::factory()->create();
    $product  = Product::factory()->create(['price' => 50.00]);

    Livewire::test('pages::orders.create')
        ->set('customerId', $customer->id)
        ->set('currentStep', 6)
        ->set('items', [
            ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 50.00, 'discount' => 0],
        ])
        ->set('shippingMethod', 'standard')
        ->set('paymentMethod', 'cash')
        ->set('currency', 'USD')
        ->set('taxRate', 0)
        ->set('priority', 'normal')
        ->set('confirm', false)
        ->call('submit')
        ->assertHasErrors(['confirm' => 'accepted']);
});

test('order saved with correct totals on submit', function () {
    $customer = Customer::factory()->create();
    $product  = Product::factory()->create(['price' => 100.00]);

    Livewire::test('pages::orders.create')
        ->set('customerId', $customer->id)
        ->set('items', [
            ['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 100.00, 'discount' => 10],
        ])
        ->set('shippingMethod', 'standard')
        ->set('paymentMethod', 'bank_transfer')
        ->set('currency', 'USD')
        ->set('taxRate', 10)
        ->set('priority', 'normal')
        ->set('confirm', true)
        ->call('submit');

    $order = Order::latest()->first();

    expect($order)->not->toBeNull()
        ->and((float) $order->subtotal)->toBe(180.00)
        ->and((float) $order->tax_amount)->toBe(18.00)
        ->and((float) $order->total)->toBe(198.00)
        ->and($order->status)->toBe('confirmed')
        ->and($order->confirmed_at)->not->toBeNull();
});

test('order items saved correctly', function () {
    $customer = Customer::factory()->create();
    $product  = Product::factory()->create(['price' => 50.00]);

    Livewire::test('pages::orders.create')
        ->set('customerId', $customer->id)
        ->set('items', [
            ['product_id' => $product->id, 'quantity' => 3, 'unit_price' => 50.00, 'discount' => 0],
        ])
        ->set('shippingMethod', 'express')
        ->set('paymentMethod', 'cash')
        ->set('currency', 'USD')
        ->set('taxRate', 0)
        ->set('priority', 'high')
        ->set('confirm', true)
        ->call('submit');

    $item = Order::latest()->first()->items()->first();

    expect($item->quantity)->toBe(3)
        ->and((float) $item->unit_price)->toBe(50.00)
        ->and((float) $item->line_total)->toBe(150.00);
});

test('subtotal computed correctly from items', function () {
    $product = Product::factory()->create(['price' => 100.00]);

    Livewire::test('pages::orders.create')
        ->set('items', [
            ['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 100.00, 'discount' => 10],
            ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 50.00, 'discount' => 0],
        ])
        ->assertSet('subtotal', 230.00); // (200 * 0.9) + 50
});

test('go to previous step allowed but not future step', function () {
    Livewire::test('pages::orders.create')
        ->set('currentStep', 3)
        ->call('goToStep', 1)
        ->assertSet('currentStep', 1)
        ->call('goToStep', 5) // can't jump forward
        ->assertSet('currentStep', 1);
});
