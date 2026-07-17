<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('order_id')
                ->relationship('order', 'id')
                ->getOptionLabelFromRecordUsing(fn ($record) => "#{$record->id} — {$record->customer->name}")
                ->searchable()
                ->preload()
                ->required(),
            TextInput::make('amount_cents')
                ->label('Amount (cents)')
                ->numeric()
                ->required()
                ->minValue(1),
            Select::make('status')
                ->options([
                    'pending' => 'Pending',
                    'completed' => 'Completed',
                    'failed' => 'Failed',
                ])
                ->required()
                ->native(false),
            Select::make('method')
                ->options([
                    'card' => 'Card',
                    'manual' => 'Manual',
                ])
                ->required()
                ->native(false),
            TextInput::make('reference')
                ->maxLength(255),
        ]);
    }
}
