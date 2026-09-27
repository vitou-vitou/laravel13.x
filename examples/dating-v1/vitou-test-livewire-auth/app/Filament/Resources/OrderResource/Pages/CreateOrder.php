<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Filament\Support\Enums\IconPosition;
use Illuminate\Support\HtmlString;

class CreateOrder extends CreateRecord
{
    use HasWizard;

    protected static string $resource = OrderResource::class;

    public function form(Form $form): Form
    {
        return $form->schema([
            Wizard::make($this->getSteps())
                ->startOnStep($this->getStartStep())
                ->cancelAction($this->getCancelFormAction())
                ->submitAction($this->getSubmitFormAction())
                ->skippable($this->hasSkippableSteps())
                ->contained(false)
                ->nextAction(
                    fn (Action $action) => $action
                        ->label('Next Step')
                        ->icon('heroicon-m-arrow-right')
                        ->iconPosition(IconPosition::After)
                )
                ->previousAction(
                    fn (Action $action) => $action
                        ->label('Back')
                        ->icon('heroicon-m-arrow-left')
                        ->color('gray')
                ),
        ])->columns(null);
    }

    protected function getSteps(): array
    {
        return [
            Step::make('Customer')
                ->description('Select or create customer')
                ->icon('heroicon-o-user')
                ->schema([
                    Select::make('customer_id')
                        ->label('Customer')
                        ->relationship('customer', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn (Set $set) => $set('shipping_address_id', null)),

                    Toggle::make('is_new_customer')
                        ->label('New customer (create inline)')
                        ->live()
                        ->hidden(fn (Get $get) => filled($get('customer_id'))),

                    Grid::make(2)
                        ->schema([
                            TextInput::make('new_customer_name')
                                ->label('Name')
                                ->required(),
                            TextInput::make('new_customer_email')
                                ->label('Email')
                                ->email()
                                ->required(),
                            TextInput::make('new_customer_phone')
                                ->label('Phone'),
                        ])
                        ->hidden(fn (Get $get) => ! $get('is_new_customer')),
                ]),

            Step::make('Items')
                ->description('Add products to order')
                ->icon('heroicon-o-shopping-cart')
                ->schema([
                    Repeater::make('items')
                        ->label('Order Items')
                        ->schema([
                            Select::make('product_id')
                                ->label('Product')
                                ->relationship('product', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                                    if (! $state) return;
                                    $product = \App\Models\Product::find($state);
                                    $set('unit_price', $product?->price ?? 0);
                                }),

                            TextInput::make('quantity')
                                ->numeric()
                                ->minValue(1)
                                ->default(1)
                                ->required()
                                ->live(debounce: 500),

                            TextInput::make('unit_price')
                                ->numeric()
                                ->prefix('$')
                                ->required()
                                ->readOnly()
                                ->live(debounce: 500),

                            TextInput::make('discount')
                                ->numeric()
                                ->suffix('%')
                                ->default(0)
                                ->minValue(0)
                                ->maxValue(100)
                                ->live(debounce: 500),

                            Placeholder::make('line_total')
                                ->label('Line Total')
                                ->content(function (Get $get): string {
                                    $qty   = (float) ($get('quantity') ?? 1);
                                    $price = (float) ($get('unit_price') ?? 0);
                                    $disc  = (float) ($get('discount') ?? 0);
                                    $total = $qty * $price * (1 - $disc / 100);
                                    return '$' . number_format($total, 2);
                                }),
                        ])
                        ->columns(5)
                        ->minItems(1)
                        ->reorderable()
                        ->cloneable()
                        ->collapsible(),
                ]),

            Step::make('Shipping')
                ->description('Delivery address and method')
                ->icon('heroicon-o-truck')
                ->schema([
                    Select::make('shipping_address_id')
                        ->label('Saved Address')
                        ->options(function (Get $get): array {
                            $customerId = $get('customer_id');
                            if (! $customerId) return [];
                            return \App\Models\CustomerAddress::where('customer_id', $customerId)
                                ->pluck('label', 'id')
                                ->toArray();
                        })
                        ->live()
                        ->placeholder('— or enter address below —'),

                    Grid::make(2)
                        ->schema([
                            TextInput::make('shipping_street')->label('Street'),
                            TextInput::make('shipping_city')->label('City'),
                            TextInput::make('shipping_state')->label('State / Province'),
                            TextInput::make('shipping_zip')->label('ZIP / Postcode'),
                            Select::make('shipping_country')
                                ->label('Country')
                                ->searchable()
                                ->options(\Symfony\Component\Intl\Countries::getNames())
                                ->default('US'),
                        ])
                        ->hidden(fn (Get $get) => filled($get('shipping_address_id'))),

                    Select::make('shipping_method')
                        ->options([
                            'standard'  => 'Standard',
                            'express'   => 'Express',
                            'overnight' => 'Overnight',
                            'pickup'    => 'Pickup',
                        ])
                        ->required()
                        ->default('standard'),

                    DatePicker::make('estimated_delivery')
                        ->label('Estimated Delivery')
                        ->minDate(now()),
                ]),

            Step::make('Payment')
                ->description('Payment method and terms')
                ->icon('heroicon-o-credit-card')
                ->schema([
                    Select::make('payment_method')
                        ->options([
                            'cash'         => 'Cash',
                            'bank_transfer' => 'Bank Transfer',
                            'credit_card'  => 'Credit Card',
                            'invoice'      => 'Invoice',
                        ])
                        ->required()
                        ->live()
                        ->default('bank_transfer'),

                    Select::make('payment_terms')
                        ->options([
                            'immediate' => 'Immediate',
                            'net_15'    => 'Net 15',
                            'net_30'    => 'Net 30',
                            'net_60'    => 'Net 60',
                        ])
                        ->visible(fn (Get $get) => $get('payment_method') === 'invoice'),

                    DatePicker::make('due_date')
                        ->label('Due Date')
                        ->minDate(now())
                        ->visible(fn (Get $get) => $get('payment_method') === 'invoice'),

                    Select::make('currency')
                        ->options([
                            'USD' => 'USD — US Dollar',
                            'EUR' => 'EUR — Euro',
                            'GBP' => 'GBP — British Pound',
                            'SGD' => 'SGD — Singapore Dollar',
                        ])
                        ->default('USD')
                        ->required()
                        ->searchable(),

                    TextInput::make('tax_rate')
                        ->label('Tax Rate (%)')
                        ->numeric()
                        ->suffix('%')
                        ->default(0)
                        ->minValue(0)
                        ->maxValue(100),
                ]),

            Step::make('Notes')
                ->description('Additional information')
                ->icon('heroicon-o-document-text')
                ->schema([
                    Select::make('priority')
                        ->options([
                            'normal' => 'Normal',
                            'high'   => 'High',
                            'urgent' => 'Urgent',
                        ])
                        ->default('normal')
                        ->required(),

                    Textarea::make('customer_notes')
                        ->label('Customer Notes (printed on invoice)')
                        ->rows(3),

                    Textarea::make('internal_notes')
                        ->label('Internal Notes (not visible to customer)')
                        ->rows(3),

                    TagsInput::make('tags')
                        ->label('Tags')
                        ->placeholder('Add tag...'),

                    FileUpload::make('attachments')
                        ->label('Attachments')
                        ->multiple()
                        ->acceptedFileTypes(['application/pdf', 'image/png', 'image/jpeg'])
                        ->maxSize(10240)
                        ->directory('order-attachments'),
                ]),

            Step::make('Review & Confirm')
                ->description('Verify before submitting')
                ->icon('heroicon-o-check-circle')
                ->schema([
                    Placeholder::make('review_customer')
                        ->label('Customer')
                        ->content(fn (Get $get): string => \App\Models\Customer::find($get('customer_id'))?->name ?? '—'),

                    Placeholder::make('review_items')
                        ->label('Items')
                        ->content(function (Get $get): HtmlString {
                            $items = collect($get('items') ?? []);
                            if ($items->isEmpty()) return new HtmlString('<em>None</em>');
                            $rows = $items->map(function ($item) {
                                $product = \App\Models\Product::find($item['product_id'] ?? null);
                                $name    = $product?->name ?? '—';
                                $qty     = $item['quantity'] ?? 0;
                                $price   = number_format($item['unit_price'] ?? 0, 2);
                                $disc    = $item['discount'] ?? 0;
                                $total   = number_format($qty * ($item['unit_price'] ?? 0) * (1 - $disc / 100), 2);
                                return "<tr><td>{$name}</td><td>{$qty}</td><td>\${$price}</td><td>{$disc}%</td><td>\${$total}</td></tr>";
                            })->join('');
                            return new HtmlString(
                                '<table class="w-full text-sm"><thead><tr><th class="text-left">Product</th><th>Qty</th><th>Price</th><th>Disc</th><th>Total</th></tr></thead><tbody>'
                                . $rows . '</tbody></table>'
                            );
                        }),

                    Placeholder::make('review_totals')
                        ->label('Order Totals')
                        ->content(function (Get $get): HtmlString {
                            $items    = collect($get('items') ?? []);
                            $subtotal = $items->sum(fn ($i) =>
                                ($i['unit_price'] ?? 0) * ($i['quantity'] ?? 0) * (1 - (($i['discount'] ?? 0) / 100))
                            );
                            $taxRate  = (float) ($get('tax_rate') ?? 0);
                            $tax      = $subtotal * $taxRate / 100;
                            $total    = $subtotal + $tax;
                            $currency = $get('currency') ?? 'USD';
                            return new HtmlString(
                                "<dl class='space-y-1 text-sm'>"
                                . "<dt class='font-medium'>Subtotal:</dt><dd>{$currency} " . number_format($subtotal, 2) . "</dd>"
                                . "<dt class='font-medium'>Tax ({$taxRate}%):</dt><dd>{$currency} " . number_format($tax, 2) . "</dd>"
                                . "<dt class='font-bold text-base'>Total:</dt><dd class='font-bold text-base'>{$currency} " . number_format($total, 2) . "</dd>"
                                . "</dl>"
                            );
                        }),

                    Placeholder::make('review_shipping')
                        ->label('Shipping Method')
                        ->content(fn (Get $get): string => ucfirst($get('shipping_method') ?? '—')),

                    Placeholder::make('review_payment')
                        ->label('Payment')
                        ->content(fn (Get $get): string => ucfirst(str_replace('_', ' ', $get('payment_method') ?? '—'))),

                    Checkbox::make('confirm')
                        ->label('I confirm this order is correct and ready to submit.')
                        ->accepted()
                        ->required(),
                ]),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $items = collect($data['items'] ?? []);

        $subtotal = $items->sum(fn ($i) =>
            ($i['unit_price'] ?? 0) * ($i['quantity'] ?? 0) * (1 - (($i['discount'] ?? 0) / 100))
        );

        $taxRate = (float) ($data['tax_rate'] ?? 0);
        $tax     = $subtotal * $taxRate / 100;

        $data['subtotal']    = $subtotal;
        $data['tax_amount']  = $tax;
        $data['shipping_cost'] = $data['shipping_cost'] ?? 0;
        $data['total']       = $subtotal + $tax + $data['shipping_cost'];
        $data['status']      = 'confirmed';
        $data['confirmed_at'] = now();

        unset($data['confirm'], $data['is_new_customer'], $data['shipping_address_id']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $order = $this->record;

        foreach ($this->data['items'] ?? [] as $item) {
            $order->items()->create([
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'discount'   => $item['discount'] ?? 0,
                'line_total' => $item['unit_price'] * $item['quantity'] * (1 - ($item['discount'] ?? 0) / 100),
            ]);
        }

        event(new \App\Events\OrderCreated($order));
    }
}
