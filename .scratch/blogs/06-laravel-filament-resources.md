# Filament Resources: Work With the Panel Lifecycle, Stop Writing Custom Admin Controllers

You install Filament for your admin dashboard, and within two weeks someone on the team creates a classic Laravel controller with custom Blade templates to handle a "complex order refund." They were uncomfortable with Filament's declarative schemas, so they bypassed the panel entirely. Now your admin has two different design languages, two separate authentication checks, and duplicate code for navigating back to the list.

Filament is not an obstacle to work around; it is a complete admin panel ecosystem. Between Resource schemas, custom Table Actions, and modal forms, Filament handles nearly every administrative workflow out of the box. If you work within Filament's lifecycle rather than escaping to custom controllers, you can ship complex administrative tools in a fraction of the time with unified authorization and design.

## Declarative Schemas Beat Hand-Rolled CRUD

In a standard Filament resource, you define the form and table interfaces declaratively. Filament automatically handles pagination, search indexing, sorting, input sanitization, and responsive column rendering.

Here is a clean definition for a customer support order resource:

app/Filament/Resources/OrderResource.php:
```php
namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('reference')
                ->disabled()
                ->required(),
            Forms\Components\Select::make('status')
                ->options([
                    'pending' => 'Pending',
                    'paid' => 'Paid',
                    'shipped' => 'Shipped',
                    'refunded' => 'Refunded',
                ])
                ->required(),
            Forms\Components\Textarea::make('internal_notes')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reference')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('customer.name')->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'primary' => 'shipped',
                        'danger' => 'refunded',
                    ]),
                Tables\Columns\TextColumn::make('total')->money('usd')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
```

This resource generates an interactive table with search, pagination, and sorting alongside an edit screen. Filament handles the database queries, validation, and styling automatically.

## Handle Complex Logic with Table Actions and Modals

When developers need custom behavior—such as processing a refund with a mandatory reason—they often assume Filament cannot handle it. Instead of creating a custom controller, use Filament Actions with embedded modal forms.

Add an action directly to your table schema:

app/Filament/Resources/OrderResource.php:
```php
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

public static function table(Table $table): Table
{
    return $table
        // ... columns definition
        ->actions([
            Action::make('refund')
                ->label('Refund')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn (Order $record): bool => auth()->user()->can('refund', $record))
                ->form([
                    Textarea::make('reason')
                        ->label('Reason for Refund')
                        ->required()
                        ->maxLength(255),
                ])
                ->action(function (Order $record, array $data): void {
                    $record->processRefund($data['reason'], auth()->user());

                    Notification::make()
                        ->title('Order Refunded')
                        ->success()
                        ->send();
                }),
        ]);
}
```

When an operator clicks "Refund," Filament displays a modal dialog asking for the required reason. When submitted, the action validates the input, executes the refund logic, and sends a notification toast without a full page reload.

## Filament Integrates with Standard Laravel Policies

You do not need to configure an external permission package or write custom gate checks to protect Filament resources. Filament automatically discovers and obeys the standard Laravel Policy associated with your Eloquent model.

If you have an `OrderPolicy`, Filament maps standard CRUD actions to policy methods:

app/Policies/OrderPolicy.php:
```php
namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'support']);
    }

    public function update(User $user, Order $order): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, Order $order): bool
    {
        return false; // Orders cannot be deleted via the panel
    }
}
```

If `viewAny` returns false, the entire resource disappears from the navigation sidebar. If `delete` returns false, Filament removes the delete button from tables and forms. Authorization remains centralized in your policy where console commands and API endpoints can also check it.

## What Can Go Wrong

A major operational hazard with admin panels is memory exhaustion caused by synchronous bulk actions. If an operator selects five hundred orders and clicks "Export to CSV" or "Mark as Shipped," a synchronous closure will exhaust PHP memory or hit the maximum execution timeout.

For operations that touch multiple records, delegate work to a queued background job:

```php
Tables\Actions\BulkAction::make('export')
    ->label('Export Selected')
    ->action(function (Collection $records): void {
        ExportOrdersJob::dispatch($records->pluck('id')->toArray(), auth()->user());

        Notification::make()
            ->title('Export Queued')
            ->body('We will email you a download link when ready.')
            ->info()
            ->send();
    });
```

The panel remains responsive, and heavy exports process safely on queue workers.

## Summary

Filament provides a battle-tested foundation for Laravel admin panels. Avoid the temptation to build custom Blade controllers when requirements get complex. Use declarative Form and Table schemas, handle custom workflows through Actions with modal forms, and rely on standard Laravel Policies for authorization.

By staying inside Filament's design and lifecycle, your administrative tooling stays consistent, secure, and rapid to develop.

## Further Reading

- [Filament Resources Documentation](https://filamentphp.com/docs/panels/resources)
- [Filament Actions and Modals](https://filamentphp.com/docs/actions/overview)
- [Filament Authorization and Policies](https://filamentphp.com/docs/panels/resources#authorization)
- [Laravel Authorization Policies](https://laravel.com/docs/authorization#creating-policies)

What is the most complex administrative workflow you've built entirely inside Filament? Tell us about it in the comments.
