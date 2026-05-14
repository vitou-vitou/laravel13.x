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

<div class="min-h-screen bg-[#111111] px-4 py-8">
    <div class="max-w-4xl mx-auto">

        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-sm text-[#888888] mb-3">
            <span>Orders</span>
            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span class="text-white">Create Order</span>
        </nav>

        {{-- Page title --}}
        <h1 class="text-2xl font-bold text-white mb-6">Create New Order</h1>

        {{-- Step bar --}}
        @php
            $steps = [
                1 => ['label' => 'Customer',   'icon' => 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z'],
                2 => ['label' => 'Items',      'icon' => 'M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z'],
                3 => ['label' => 'Shipping',   'icon' => 'M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12'],
                4 => ['label' => 'Payment',    'icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z'],
                5 => ['label' => 'Notes',      'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z'],
                6 => ['label' => 'Review',     'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            ];
            $stepSubtitles = [
                1 => 'Select or create a customer',
                2 => 'Add products to your order',
                3 => 'Choose delivery address and method',
                4 => 'Set payment method and terms',
                5 => 'Add notes and set priority',
                6 => 'Review and confirm your order',
            ];
        @endphp

        <div class="bg-[#1e1e1e] rounded-xl px-6 py-5 mb-5">
            <div class="flex items-center justify-between">
                @foreach($steps as $n => $step)
                    <button
                        type="button"
                        wire:click="goToStep({{ $n }})"
                        class="flex items-center gap-3 {{ $n < $currentStep ? 'cursor-pointer' : 'cursor-default' }}"
                    >
                        {{-- Icon circle --}}
                        <div @class([
                            'w-10 h-10 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-colors',
                            'border-[#00bfa5] text-[#00bfa5]' => $n === $currentStep,
                            'border-[#00bfa5] text-[#00bfa5] opacity-80' => $n < $currentStep,
                            'border-[#3a3a3a] text-[#555555]' => $n > $currentStep,
                        ])>
                            @if($n < $currentStep)
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                </svg>
                            @else
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $step['icon'] }}"/>
                                </svg>
                            @endif
                        </div>
                        {{-- Label --}}
                        <span @class([
                            'text-sm font-medium hidden sm:block',
                            'text-[#00bfa5]' => $n <= $currentStep,
                            'text-[#555555]' => $n > $currentStep,
                        ])>{{ $step['label'] }}</span>
                    </button>

                    @if($n < 6)
                        <div @class([
                            'flex-1 h-px mx-3',
                            'bg-[#00bfa5] opacity-40' => $n < $currentStep,
                            'bg-[#2e2e2e]' => $n >= $currentStep,
                        ])></div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Form card --}}
        <div class="bg-[#1e1e1e] rounded-xl overflow-hidden">

            {{-- Card header --}}
            <div class="px-6 pt-6 pb-5">
                <h2 class="text-lg font-semibold text-white">{{ $steps[$currentStep]['label'] }}</h2>
                <p class="text-sm text-[#888888] mt-1">{{ $stepSubtitles[$currentStep] }}</p>
            </div>
            <div class="border-t border-[#2e2e2e]"></div>

            {{-- Card body --}}
            <div class="px-6 py-6">

                @php
                    $inputClass = 'w-full bg-[#2a2a2a] border border-[#3a3a3a] rounded-lg px-4 py-2.5 text-white text-sm placeholder-[#555555] focus:outline-none focus:border-[#00bfa5] focus:ring-1 focus:ring-[#00bfa5] transition-colors';
                    $selectClass = 'w-full bg-[#2a2a2a] border border-[#3a3a3a] rounded-lg px-4 py-2.5 text-white text-sm focus:outline-none focus:border-[#00bfa5] focus:ring-1 focus:ring-[#00bfa5] transition-colors appearance-none';
                    $labelClass = 'block text-sm text-[#cccccc] mb-1.5';
                    $errorClass = 'mt-1 text-xs text-red-400';
                @endphp

                {{-- Step 1: Customer --}}
                @if($currentStep === 1)
                    <div class="space-y-5">
                        <div>
                            <label class="{{ $labelClass }}">Select Customer <span class="text-red-500">*</span></label>
                            <select wire:model.live="customerId" class="{{ $selectClass }}">
                                <option value="">— Select a customer —</option>
                                @foreach($this->customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->email }})</option>
                                @endforeach
                            </select>
                            @error('customerId') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                        </div>

                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" wire:model.live="isNewCustomer" class="w-4 h-4 rounded border-[#3a3a3a] bg-[#2a2a2a] text-[#00bfa5] focus:ring-[#00bfa5]" />
                            <span class="text-sm text-[#cccccc]">Create new customer instead</span>
                        </label>

                        @if($isNewCustomer)
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-1">
                                <div>
                                    <label class="{{ $labelClass }}">Full Name <span class="text-red-500">*</span></label>
                                    <input wire:model="newCustomerName" type="text" placeholder="John Doe" class="{{ $inputClass }}" />
                                    @error('newCustomerName') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="{{ $labelClass }}">Email Address <span class="text-red-500">*</span></label>
                                    <input wire:model="newCustomerEmail" type="email" placeholder="john@example.com" class="{{ $inputClass }}" />
                                    @error('newCustomerEmail') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="{{ $labelClass }}">Phone Number</label>
                                    <input wire:model="newCustomerPhone" type="text" placeholder="+1 555 000 0000" class="{{ $inputClass }}" />
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Step 2: Items --}}
                @if($currentStep === 2)
                    <div class="space-y-4">
                        @foreach($items as $index => $item)
                            <div class="bg-[#252525] border border-[#2e2e2e] rounded-lg p-4">
                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
                                    <div class="sm:col-span-4">
                                        <label class="{{ $labelClass }}">Product <span class="text-red-500">*</span></label>
                                        <select wire:model="items.{{ $index }}.product_id" wire:change="fillPriceFromProduct({{ $index }})" class="{{ $selectClass }}">
                                            <option value="">— Select product —</option>
                                            @foreach($this->products as $product)
                                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('items.{{ $index }}.product_id') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="{{ $labelClass }}">Qty <span class="text-red-500">*</span></label>
                                        <input wire:model.live="items.{{ $index }}.quantity" type="number" min="1" class="{{ $inputClass }}" />
                                        @error('items.{{ $index }}.quantity') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="{{ $labelClass }}">Unit Price</label>
                                        <input wire:model.live="items.{{ $index }}.unit_price" type="number" step="0.01" min="0" readonly class="{{ $inputClass }} opacity-60 cursor-not-allowed" />
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="{{ $labelClass }}">Discount %</label>
                                        <input wire:model.live="items.{{ $index }}.discount" type="number" step="0.01" min="0" max="100" placeholder="0" class="{{ $inputClass }}" />
                                    </div>
                                    <div class="sm:col-span-1 text-right">
                                        <p class="text-xs text-[#888] mb-1.5">Total</p>
                                        <p class="text-sm font-semibold text-[#00bfa5]">${{ number_format($this->lineTotal[$index] ?? 0, 2) }}</p>
                                    </div>
                                    <div class="sm:col-span-1 flex justify-end">
                                        @if(count($items) > 1)
                                            <button type="button" wire:click="removeItem({{ $index }})" class="p-2 text-[#555] hover:text-red-400 transition-colors rounded-lg hover:bg-[#2e2e2e]">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        @error('items') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror

                        <button type="button" wire:click="addItem" class="flex items-center gap-2 text-sm text-[#00bfa5] hover:text-white transition-colors px-3 py-2 rounded-lg hover:bg-[#252525] border border-dashed border-[#3a3a3a] hover:border-[#00bfa5] w-full justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            Add Item
                        </button>

                        <div class="pt-2 border-t border-[#2e2e2e] flex justify-end">
                            <p class="text-sm text-[#888]">Subtotal: <span class="font-semibold text-white ml-2">${{ number_format($this->subtotal, 2) }}</span></p>
                        </div>
                    </div>
                @endif

                {{-- Step 3: Shipping --}}
                @if($currentStep === 3)
                    <div class="space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div class="sm:col-span-2">
                                <label class="{{ $labelClass }}">Street Address</label>
                                <input wire:model="shippingStreet" type="text" placeholder="123 Main Street" class="{{ $inputClass }}" />
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">City</label>
                                <input wire:model="shippingCity" type="text" class="{{ $inputClass }}" />
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">State / Province</label>
                                <input wire:model="shippingState" type="text" class="{{ $inputClass }}" />
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">ZIP / Postcode</label>
                                <input wire:model="shippingZip" type="text" class="{{ $inputClass }}" />
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">Country</label>
                                <input wire:model="shippingCountry" type="text" class="{{ $inputClass }}" />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="{{ $labelClass }}">Shipping Method <span class="text-red-500">*</span></label>
                                <select wire:model="shippingMethod" class="{{ $selectClass }}">
                                    <option value="standard">Standard</option>
                                    <option value="express">Express</option>
                                    <option value="overnight">Overnight</option>
                                    <option value="pickup">Pickup</option>
                                </select>
                                @error('shippingMethod') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">Estimated Delivery</label>
                                <input wire:model="estimatedDelivery" type="date" class="{{ $inputClass }}" />
                                @error('estimatedDelivery') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Step 4: Payment --}}
                @if($currentStep === 4)
                    <div class="space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="{{ $labelClass }}">Payment Method <span class="text-red-500">*</span></label>
                                <select wire:model.live="paymentMethod" class="{{ $selectClass }}">
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="credit_card">Credit Card</option>
                                    <option value="invoice">Invoice</option>
                                </select>
                                @error('paymentMethod') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">Currency <span class="text-red-500">*</span></label>
                                <select wire:model="currency" class="{{ $selectClass }}">
                                    <option value="USD">USD — US Dollar</option>
                                    <option value="EUR">EUR — Euro</option>
                                    <option value="GBP">GBP — British Pound</option>
                                    <option value="SGD">SGD — Singapore Dollar</option>
                                </select>
                                @error('currency') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        @if($paymentMethod === 'invoice')
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div>
                                    <label class="{{ $labelClass }}">Payment Terms <span class="text-red-500">*</span></label>
                                    <select wire:model="paymentTerms" class="{{ $selectClass }}">
                                        <option value="">— Select —</option>
                                        <option value="immediate">Immediate</option>
                                        <option value="net_15">Net 15</option>
                                        <option value="net_30">Net 30</option>
                                        <option value="net_60">Net 60</option>
                                    </select>
                                    @error('paymentTerms') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="{{ $labelClass }}">Due Date <span class="text-red-500">*</span></label>
                                    <input wire:model="dueDate" type="date" class="{{ $inputClass }}" />
                                    @error('dueDate') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        @endif

                        <div>
                            <label class="{{ $labelClass }}">Tax Rate (%)</label>
                            <input wire:model.live="taxRate" type="number" step="0.01" min="0" max="100" placeholder="0" class="{{ $inputClass }} sm:w-1/2" />
                            @error('taxRate') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                        </div>
                    </div>
                @endif

                {{-- Step 5: Notes --}}
                @if($currentStep === 5)
                    <div class="space-y-5">
                        <div>
                            <label class="{{ $labelClass }}">Priority</label>
                            <select wire:model="priority" class="{{ $selectClass }} sm:w-1/2">
                                <option value="normal">Normal</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Customer Notes <span class="text-[#555] text-xs">(printed on invoice)</span></label>
                            <textarea wire:model="customerNotes" rows="3" class="{{ $inputClass }} resize-none" placeholder="Notes visible to the customer..."></textarea>
                            @error('customerNotes') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">Internal Notes <span class="text-[#555] text-xs">(not visible to customer)</span></label>
                            <textarea wire:model="internalNotes" rows="3" class="{{ $inputClass }} resize-none" placeholder="Internal staff notes..."></textarea>
                            @error('internalNotes') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                        </div>
                    </div>
                @endif

                {{-- Step 6: Review & Confirm --}}
                @if($currentStep === 6)
                    <div class="space-y-1">
                        @php
                            $rows = [
                                'Customer'  => $customerId ? ($this->customers->firstWhere('id', $customerId)?->name ?? '—') : ($isNewCustomer ? $newCustomerName . ' (new)' : '—'),
                                'Shipping'  => ucfirst($shippingMethod),
                                'Payment'   => ucfirst(str_replace('_', ' ', $paymentMethod)) . ($paymentMethod === 'invoice' && $paymentTerms ? ' — ' . $paymentTerms : ''),
                                'Currency'  => $currency,
                                'Priority'  => ucfirst($priority),
                            ];
                        @endphp

                        @foreach($rows as $key => $val)
                            <div class="flex items-center justify-between py-3 border-b border-[#2e2e2e]">
                                <span class="text-sm text-[#888]">{{ $key }}</span>
                                <span class="text-sm text-white font-medium">{{ $val }}</span>
                            </div>
                        @endforeach

                        {{-- Items summary --}}
                        <div class="py-3 border-b border-[#2e2e2e]">
                            <p class="text-sm text-[#888] mb-2">Items</p>
                            @foreach($items as $i => $item)
                                @php $p = $this->products->firstWhere('id', $item['product_id']) @endphp
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-[#cccccc]">{{ $p?->name ?? '—' }} × {{ $item['quantity'] }}@if(($item['discount'] ?? 0) > 0) <span class="text-[#00bfa5] text-xs">−{{ $item['discount'] }}%</span>@endif</span>
                                    <span class="text-white font-medium">${{ number_format($this->lineTotal[$i] ?? 0, 2) }}</span>
                                </div>
                            @endforeach
                        </div>

                        {{-- Totals --}}
                        <div class="pt-2 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-[#888]">Subtotal</span>
                                <span class="text-white">{{ $currency }} {{ number_format($this->subtotal, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-[#888]">Tax ({{ $taxRate }}%)</span>
                                <span class="text-white">{{ $currency }} {{ number_format($this->taxAmount, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-base font-bold pt-2 border-t border-[#2e2e2e]">
                                <span class="text-white">Total</span>
                                <span class="text-[#00bfa5]">{{ $currency }} {{ number_format($this->total, 2) }}</span>
                            </div>
                        </div>

                        {{-- Confirm checkbox --}}
                        <div class="pt-5">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="confirm" class="mt-0.5 w-4 h-4 rounded border-[#3a3a3a] bg-[#2a2a2a] text-[#00bfa5] focus:ring-[#00bfa5]" />
                                <span class="text-sm text-[#cccccc]">I confirm this order is correct and ready to submit.</span>
                            </label>
                            @error('confirm') <p class="{{ $errorClass }} ml-7">{{ $message }}</p> @enderror
                        </div>
                    </div>
                @endif

            </div>

            {{-- Card footer — Back / Next buttons --}}
            <div class="border-t border-[#2e2e2e] px-6 py-4 flex items-center justify-between">
                <div>
                    @if($currentStep > 1)
                        <button type="button" wire:click="previousStep" class="flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-[#cccccc] bg-[#2a2a2a] border border-[#3a3a3a] rounded-lg hover:bg-[#333] hover:text-white transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                            Back
                        </button>
                    @endif
                </div>
                <div>
                    @if($currentStep < $totalSteps)
                        <button type="button" wire:click="nextStep" class="flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-[#111] bg-[#00bfa5] rounded-lg hover:bg-[#00d4b8] transition-colors">
                            Next Step
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </button>
                    @else
                        <button type="button" wire:click="submit" class="flex items-center gap-2 px-6 py-2.5 text-sm font-medium text-[#111] bg-[#00bfa5] rounded-lg hover:bg-[#00d4b8] transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            Place Order
                        </button>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
