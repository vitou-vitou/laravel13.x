<?php

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    public int $currentStep = 1;
    public int $totalSteps  = 6;

    // Step 1 — Customer
    #[Validate('required|exists:customers,id')]
    public ?int $customerId = null;

    public bool $isNewCustomer = false;

    #[Validate('required_if:isNewCustomer,true|nullable|string|max:255')]
    public string $newCustomerName = '';

    #[Validate('required_if:isNewCustomer,true|nullable|email|max:255')]
    public string $newCustomerEmail = '';

    #[Validate('nullable|string|max:50')]
    public string $newCustomerPhone = '';

    // Step 2 — Items
    public array $items = [];

    // Step 3 — Shipping
    #[Validate('nullable|string|max:255')]
    public string $shippingStreet  = '';

    #[Validate('nullable|string|max:100')]
    public string $shippingCity    = '';

    #[Validate('nullable|string|max:100')]
    public string $shippingState   = '';

    #[Validate('nullable|string|max:20')]
    public string $shippingZip     = '';

    #[Validate('nullable|string|max:100')]
    public string $shippingCountry = 'US';

    #[Validate('required|in:standard,express,overnight,pickup')]
    public string $shippingMethod  = 'standard';

    #[Validate('nullable|date|after_or_equal:today')]
    public string $estimatedDelivery = '';

    // Step 4 — Payment
    #[Validate('required|in:cash,bank_transfer,credit_card,invoice')]
    public string $paymentMethod = 'bank_transfer';

    #[Validate('required_if:paymentMethod,invoice|nullable|in:immediate,net_15,net_30,net_60')]
    public string $paymentTerms = '';

    #[Validate('required_if:paymentMethod,invoice|nullable|date|after_or_equal:today')]
    public string $dueDate = '';

    #[Validate('required|in:USD,EUR,GBP,SGD')]
    public string $currency = 'USD';

    #[Validate('required|numeric|min:0|max:100')]
    public float $taxRate = 0;

    // Step 5 — Notes
    #[Validate('required|in:normal,high,urgent')]
    public string $priority = 'normal';

    #[Validate('nullable|string|max:2000')]
    public string $customerNotes  = '';

    #[Validate('nullable|string|max:2000')]
    public string $internalNotes  = '';

    // Step 6 — Confirm
    #[Validate('accepted')]
    public bool $confirm = false;

    /** @var array<int,string[]> */
    protected array $stepFields = [
        1 => ['customerId', 'isNewCustomer', 'newCustomerName', 'newCustomerEmail', 'newCustomerPhone'],
        2 => ['items', 'items.*.product_id', 'items.*.quantity', 'items.*.unit_price', 'items.*.discount'],
        3 => ['shippingStreet', 'shippingCity', 'shippingState', 'shippingZip', 'shippingCountry', 'shippingMethod', 'estimatedDelivery'],
        4 => ['paymentMethod', 'paymentTerms', 'dueDate', 'currency', 'taxRate'],
        5 => ['priority', 'customerNotes', 'internalNotes'],
        6 => ['confirm'],
    ];

    /** @var array<string,string> */
    protected array $itemRules = [
        'items'              => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity'   => 'required|integer|min:1',
        'items.*.unit_price' => 'required|numeric|min:0',
        'items.*.discount'   => 'nullable|numeric|min:0|max:100',
    ];

    public function mount(): void
    {
        $this->items = [
            ['product_id' => null, 'quantity' => 1, 'unit_price' => 0, 'discount' => 0],
        ];
    }

    public function updatedCustomerId(): void
    {
        $this->isNewCustomer = false;
    }

    public function addItem(): void
    {
        $this->items[] = ['product_id' => null, 'quantity' => 1, 'unit_price' => 0, 'discount' => 0];
    }

    public function removeItem(int $index): void
    {
        array_splice($this->items, $index, 1);
        $this->items = array_values($this->items);
    }

    public function fillPriceFromProduct(int $index): void
    {
        $productId = $this->items[$index]['product_id'] ?? null;
        if ($productId) {
            $product = Product::find($productId);
            $this->items[$index]['unit_price'] = $product ? (float) $product->price : 0;
        }
    }

    #[Computed]
    public function customers(): \Illuminate\Database\Eloquent\Collection
    {
        return Customer::orderBy('name')->get();
    }

    #[Computed]
    public function products(): \Illuminate\Database\Eloquent\Collection
    {
        return Product::orderBy('name')->get();
    }

    #[Computed]
    public function subtotal(): float
    {
        return collect($this->items)->sum(fn ($i) =>
            (float) ($i['unit_price'] ?? 0) * (int) ($i['quantity'] ?? 0) * (1 - ((float) ($i['discount'] ?? 0) / 100))
        );
    }

    #[Computed]
    public function taxAmount(): float
    {
        return $this->subtotal * $this->taxRate / 100;
    }

    #[Computed]
    public function total(): float
    {
        return $this->subtotal + $this->taxAmount;
    }

    #[Computed]
    public function lineTotal(): array
    {
        return collect($this->items)->map(fn ($i) =>
            (float) ($i['unit_price'] ?? 0) * (int) ($i['quantity'] ?? 0) * (1 - ((float) ($i['discount'] ?? 0) / 100))
        )->toArray();
    }

    public function nextStep(): void
    {
        $this->validateCurrentStep();

        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function goToStep(int $step): void
    {
        if ($step < $this->currentStep) {
            $this->currentStep = $step;
        }
    }

    public function submit(): void
    {
        $this->validate(['confirm' => 'accepted']);

        if ($this->isNewCustomer) {
            $this->validate([
                'newCustomerName'  => 'required|string|max:255',
                'newCustomerEmail' => 'required|email|max:255',
            ]);
            $customer = Customer::create([
                'name'  => $this->newCustomerName,
                'email' => $this->newCustomerEmail,
                'phone' => $this->newCustomerPhone,
            ]);
            $this->customerId = $customer->id;
        }

        $order = Order::create([
            'customer_id'        => $this->customerId,
            'status'             => 'confirmed',
            'subtotal'           => $this->subtotal,
            'tax_amount'         => $this->taxAmount,
            'shipping_cost'      => 0,
            'total'              => $this->total,
            'currency'           => $this->currency,
            'payment_method'     => $this->paymentMethod,
            'payment_terms'      => $this->paymentMethod === 'invoice' ? $this->paymentTerms : null,
            'due_date'           => $this->paymentMethod === 'invoice' ? $this->dueDate : null,
            'shipping_method'    => $this->shippingMethod,
            'shipping_address'   => [
                'street'  => $this->shippingStreet,
                'city'    => $this->shippingCity,
                'state'   => $this->shippingState,
                'zip'     => $this->shippingZip,
                'country' => $this->shippingCountry,
            ],
            'estimated_delivery' => $this->estimatedDelivery ?: null,
            'priority'           => $this->priority,
            'customer_notes'     => $this->customerNotes,
            'internal_notes'     => $this->internalNotes,
            'tax_rate'           => $this->taxRate,
            'confirmed_at'       => now(),
        ]);

        foreach ($this->items as $item) {
            $lineTotal = (float) $item['unit_price'] * (int) $item['quantity'] * (1 - ((float) ($item['discount'] ?? 0) / 100));
            $order->items()->create([
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'discount'   => $item['discount'] ?? 0,
                'line_total' => $lineTotal,
            ]);
        }

        $this->redirect(route('orders.show', $order), navigate: true);
    }

    private function validateCurrentStep(): void
    {
        $step = $this->currentStep;

        if ($step === 2) {
            $this->validate($this->itemRules);
            return;
        }

        $fields = $this->stepFields[$step] ?? [];
        $allRules = $this->getRules();
        $rules = array_filter($allRules, fn ($key) =>
            collect($fields)->contains(fn ($f) => $key === $f || str_starts_with($key, $f . '.')),
            ARRAY_FILTER_USE_KEY
        );

        if (! empty($rules)) {
            $this->validate($rules);
        }
    }
};
?>

<div class="max-w-3xl mx-auto py-8 px-4">

    {{-- Step progress bar --}}
    <div class="mb-8">
        <div class="flex items-center justify-between">
            @foreach ([1 => 'Customer', 2 => 'Items', 3 => 'Shipping', 4 => 'Payment', 5 => 'Notes', 6 => 'Review'] as $step => $label)
                <button
                    wire:click="goToStep({{ $step }})"
                    @class([
                        'flex flex-col items-center gap-1',
                        'cursor-pointer' => $step < $currentStep,
                        'cursor-default' => $step >= $currentStep,
                    ])
                    type="button"
                >
                    <span @class([
                        'flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold transition-colors',
                        'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' => $step < $currentStep,
                        'bg-zinc-900 text-white ring-2 ring-offset-2 ring-zinc-900 dark:bg-white dark:text-zinc-900 dark:ring-white' => $step === $currentStep,
                        'bg-zinc-200 text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400' => $step > $currentStep,
                    ])>
                        @if($step < $currentStep)
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        @else
                            {{ $step }}
                        @endif
                    </span>
                    <span @class([
                        'hidden sm:block text-xs font-medium',
                        'text-zinc-900 dark:text-white' => $step <= $currentStep,
                        'text-zinc-400 dark:text-zinc-500' => $step > $currentStep,
                    ])>{{ $label }}</span>
                </button>

                @if($step < 6)
                    <div @class([
                        'flex-1 h-0.5 mx-2',
                        'bg-zinc-900 dark:bg-white' => $step < $currentStep,
                        'bg-zinc-200 dark:bg-zinc-700' => $step >= $currentStep,
                    ])></div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- Step content --}}
    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6 shadow-sm">

        {{-- Step 1: Customer --}}
        @if($currentStep === 1)
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-6">Customer</h2>
            <div class="space-y-4">
                <flux:field>
                    <flux:label>Select Customer</flux:label>
                    <select wire:model.live="customerId" placeholder="Search customer...">
                        @foreach($this->customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->email }})</option>
                        @endforeach
                    </select>
                    <flux:error name="customerId" />
                </flux:field>
                <flux:field>
                    <flux:checkbox wire:model.live="isNewCustomer" label="Create new customer instead" />
                </flux:field>
                @if($isNewCustomer)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                        <flux:field>
                            <flux:label>Name <span class="text-red-500 ml-0.5">*</span></flux:label>
                            <flux:input wire:model="newCustomerName" placeholder="Full name" />
                            <flux:error name="newCustomerName" />
                        </flux:field>
                        <flux:field>
                            <flux:label>Email <span class="text-red-500 ml-0.5">*</span></flux:label>
                            <flux:input wire:model="newCustomerEmail" type="email" placeholder="email@example.com" />
                            <flux:error name="newCustomerEmail" />
                        </flux:field>
                        <flux:field>
                            <flux:label>Phone</flux:label>
                            <flux:input wire:model="newCustomerPhone" placeholder="+1 555 000 0000" />
                        </flux:field>
                    </div>
                @endif
            </div>
        @endif

        {{-- Step 2: Items --}}
        @if($currentStep === 2)
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-6">Order Items</h2>
            <div class="space-y-3">
                @foreach($items as $index => $item)
                    <div class="grid grid-cols-12 gap-2 items-end rounded-lg border border-zinc-100 dark:border-zinc-800 p-3">
                        <div class="col-span-12 sm:col-span-4">
                            <flux:field>
                                <flux:label>Product</flux:label>
                                <select wire:model="items.{{ $index }}.product_id" wire:change="fillPriceFromProduct({{ $index }})">
                                    <option value="">Select...</option>
                                    @foreach($this->products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                                <flux:error name="items.{{ $index }}.product_id" />
                            </flux:field>
                        </div>
                        <div class="col-span-4 sm:col-span-2">
                            <flux:field>
                                <flux:label>Qty</flux:label>
                                <flux:input wire:model.live="items.{{ $index }}.quantity" type="number" min="1" />
                                <flux:error name="items.{{ $index }}.quantity" />
                            </flux:field>
                        </div>
                        <div class="col-span-4 sm:col-span-2">
                            <flux:field>
                                <flux:label>Unit Price</flux:label>
                                <flux:input wire:model.live="items.{{ $index }}.unit_price" type="number" step="0.01" min="0" readonly />
                            </flux:field>
                        </div>
                        <div class="col-span-3 sm:col-span-2">
                            <flux:field>
                                <flux:label>Disc %</flux:label>
                                <flux:input wire:model.live="items.{{ $index }}.discount" type="number" step="0.01" min="0" max="100" />
                            </flux:field>
                        </div>
                        <div class="col-span-3 sm:col-span-1 text-sm text-zinc-600 dark:text-zinc-300 pb-2">
                            ${{ number_format($this->lineTotal[$index] ?? 0, 2) }}
                        </div>
                        <div class="col-span-2 sm:col-span-1 flex justify-end pb-1">
                            @if(count($items) > 1)
                                <flux:button wire:click="removeItem({{ $index }})" variant="ghost" size="sm" icon="trash" type="button" />
                            @endif
                        </div>
                    </div>
                @endforeach
                <flux:error name="items" />
                <flux:button wire:click="addItem" variant="ghost" icon="plus" size="sm" type="button">Add Item</flux:button>
                <div class="pt-2 text-right text-sm text-zinc-600 dark:text-zinc-300">
                    Subtotal: <span class="font-semibold text-zinc-900 dark:text-white">${{ number_format($this->subtotal, 2) }}</span>
                </div>
            </div>
        @endif

        {{-- Step 3: Shipping --}}
        @if($currentStep === 3)
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-6">Shipping</h2>
            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <flux:field class="sm:col-span-2">
                        <flux:label>Street</flux:label>
                        <flux:input wire:model="shippingStreet" placeholder="123 Main St" />
                    </flux:field>
                    <flux:field>
                        <flux:label>City</flux:label>
                        <flux:input wire:model="shippingCity" />
                    </flux:field>
                    <flux:field>
                        <flux:label>State / Province</flux:label>
                        <flux:input wire:model="shippingState" />
                    </flux:field>
                    <flux:field>
                        <flux:label>ZIP / Postcode</flux:label>
                        <flux:input wire:model="shippingZip" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Country</flux:label>
                        <flux:input wire:model="shippingCountry" />
                    </flux:field>
                </div>
                <flux:field>
                    <flux:label>Shipping Method <span class="text-red-500 ml-0.5">*</span></flux:label>
                    <select wire:model="shippingMethod">
                        <option value="standard">Standard</option>
                        <option value="express">Express</option>
                        <option value="overnight">Overnight</option>
                        <option value="pickup">Pickup</option>
                    </select>
                    <flux:error name="shippingMethod" />
                </flux:field>
                <flux:field>
                    <flux:label>Estimated Delivery</flux:label>
                    <flux:input wire:model="estimatedDelivery" type="date" />
                    <flux:error name="estimatedDelivery" />
                </flux:field>
            </div>
        @endif

        {{-- Step 4: Payment --}}
        @if($currentStep === 4)
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-6">Payment</h2>
            <div class="space-y-4">
                <flux:field>
                    <flux:label>Payment Method <span class="text-red-500 ml-0.5">*</span></flux:label>
                    <select wire:model.live="paymentMethod">
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="invoice">Invoice</option>
                    </select>
                    <flux:error name="paymentMethod" />
                </flux:field>
                @if($paymentMethod === 'invoice')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>Payment Terms <span class="text-red-500 ml-0.5">*</span></flux:label>
                            <select wire:model="paymentTerms">
                                <option value="immediate">Immediate</option>
                                <option value="net_15">Net 15</option>
                                <option value="net_30">Net 30</option>
                                <option value="net_60">Net 60</option>
                            </select>
                            <flux:error name="paymentTerms" />
                        </flux:field>
                        <flux:field>
                            <flux:label>Due Date <span class="text-red-500 ml-0.5">*</span></flux:label>
                            <flux:input wire:model="dueDate" type="date" />
                            <flux:error name="dueDate" />
                        </flux:field>
                    </div>
                @endif
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>Currency <span class="text-red-500 ml-0.5">*</span></flux:label>
                        <select wire:model="currency">
                            <option value="USD">USD — US Dollar</option>
                            <option value="EUR">EUR — Euro</option>
                            <option value="GBP">GBP — British Pound</option>
                            <option value="SGD">SGD — Singapore Dollar</option>
                        </select>
                        <flux:error name="currency" />
                    </flux:field>
                    <flux:field>
                        <flux:label>Tax Rate (%)</flux:label>
                        <flux:input wire:model.live="taxRate" type="number" step="0.01" min="0" max="100" />
                        <flux:error name="taxRate" />
                    </flux:field>
                </div>
            </div>
        @endif

        {{-- Step 5: Notes --}}
        @if($currentStep === 5)
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-6">Notes</h2>
            <div class="space-y-4">
                <flux:field>
                    <flux:label>Priority</flux:label>
                    <select wire:model="priority">
                        <option value="normal">Normal</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </flux:field>
                <flux:field>
                    <flux:label>Customer Notes</flux:label>
                    <flux:textarea wire:model="customerNotes" rows="3" />
                    <flux:error name="customerNotes" />
                </flux:field>
                <flux:field>
                    <flux:label>Internal Notes</flux:label>
                    <flux:textarea wire:model="internalNotes" rows="3" />
                    <flux:error name="internalNotes" />
                </flux:field>
            </div>
        @endif

        {{-- Step 6: Review & Confirm --}}
        @if($currentStep === 6)
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-6">Review & Confirm</h2>
            <dl class="divide-y divide-zinc-100 dark:divide-zinc-800 text-sm">
                <div class="py-3 grid grid-cols-3 gap-4">
                    <dt class="font-medium text-zinc-500 dark:text-zinc-400">Customer</dt>
                    <dd class="col-span-2 text-zinc-900 dark:text-white">
                        @if($customerId)
                            {{ $this->customers->firstWhere('id', $customerId)?->name ?? '—' }}
                        @elseif($isNewCustomer)
                            {{ $newCustomerName }} (new)
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div class="py-3 grid grid-cols-3 gap-4">
                    <dt class="font-medium text-zinc-500 dark:text-zinc-400">Items</dt>
                    <dd class="col-span-2 text-zinc-900 dark:text-white space-y-1">
                        @foreach($items as $i => $item)
                            @php $p = $this->products->firstWhere('id', $item['product_id']) @endphp
                            <div>{{ $p?->name ?? '—' }} × {{ $item['quantity'] }} @ ${{ number_format($item['unit_price'], 2) }}
                                @if(($item['discount'] ?? 0) > 0)<span class="text-zinc-400"> −{{ $item['discount'] }}%</span>@endif
                                = <span class="font-medium">${{ number_format($this->lineTotal[$i] ?? 0, 2) }}</span>
                            </div>
                        @endforeach
                    </dd>
                </div>
                <div class="py-3 grid grid-cols-3 gap-4">
                    <dt class="font-medium text-zinc-500 dark:text-zinc-400">Shipping</dt>
                    <dd class="col-span-2 text-zinc-900 dark:text-white">{{ ucfirst($shippingMethod) }}</dd>
                </div>
                <div class="py-3 grid grid-cols-3 gap-4">
                    <dt class="font-medium text-zinc-500 dark:text-zinc-400">Payment</dt>
                    <dd class="col-span-2 text-zinc-900 dark:text-white">{{ ucfirst(str_replace('_', ' ', $paymentMethod)) }}
                        @if($paymentMethod === 'invoice' && $paymentTerms) — {{ $paymentTerms }}@endif
                    </dd>
                </div>
                <div class="py-3 grid grid-cols-3 gap-4">
                    <dt class="font-medium text-zinc-500 dark:text-zinc-400">Subtotal</dt>
                    <dd class="col-span-2 text-zinc-900 dark:text-white">{{ $currency }} {{ number_format($this->subtotal, 2) }}</dd>
                </div>
                <div class="py-3 grid grid-cols-3 gap-4">
                    <dt class="font-medium text-zinc-500 dark:text-zinc-400">Tax ({{ $taxRate }}%)</dt>
                    <dd class="col-span-2 text-zinc-900 dark:text-white">{{ $currency }} {{ number_format($this->taxAmount, 2) }}</dd>
                </div>
                <div class="py-3 grid grid-cols-3 gap-4">
                    <dt class="font-bold text-zinc-900 dark:text-white">Total</dt>
                    <dd class="col-span-2 font-bold text-zinc-900 dark:text-white text-base">{{ $currency }} {{ number_format($this->total, 2) }}</dd>
                </div>
            </dl>
            <div class="mt-6">
                <flux:field>
                    <flux:checkbox wire:model="confirm" label="I confirm this order is correct and ready to submit." />
                    <flux:error name="confirm" />
                </flux:field>
            </div>
        @endif

    </div>

    {{-- Navigation buttons --}}
    <div class="mt-6 flex items-center justify-between">
        <div>
            @if($currentStep > 1)
                <flux:button wire:click="previousStep" variant="ghost" icon="arrow-left" type="button">
                    Back
                </flux:button>
            @endif
        </div>
        <div>
            @if($currentStep < $totalSteps)
                <flux:button wire:click="nextStep" icon-trailing="arrow-right" type="button">
                    Next Step
                </flux:button>
            @else
                <flux:button wire:click="submit" variant="primary" icon="check" type="button">
                    Place Order
                </flux:button>
            @endif
        </div>
    </div>

</div>
