<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                    ]),
                TextInput::make('amount_cents')
                    ->label('Amount (cents)')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->helperText('Stored in cents — e.g. 1999 = $19.99'),
                Select::make('status')
                    ->options([
                        'paid' => 'Paid',
                        'pending' => 'Pending',
                        'refunded' => 'Refunded',
                    ])
                    ->required()
                    ->native(false),
                DateTimePicker::make('ordered_at')
                    ->required()
                    ->default(now()),
            ]);
    }
}
