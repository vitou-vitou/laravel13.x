<?php

namespace App\Filament\Pages;

use App\Events\NewOrderCreated;
use App\Models\Order;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\File;
use UnitEnum;

class Playground extends Page
{
    protected static ?string $navigationLabel = 'Playground';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    protected static string|UnitEnum|null $navigationGroup = 'Development';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Playground';

    protected string $view = 'filament.pages.playground';

    public static function shouldRegisterNavigation(): bool
    {
        return static::playgroundEnabled();
    }

    public static function canAccess(): bool
    {
        return static::playgroundEnabled();
    }

    protected static function playgroundEnabled(): bool
    {
        return app()->environment(['local', 'testing']);
    }

    /**
     * @return array<string, mixed>
     */
    public function getSnapshot(): array
    {
        $hotPath = public_path('hot');
        $hot = File::exists($hotPath) ? trim(File::get($hotPath)) : null;

        return [
            'app_url' => config('app.url'),
            'vite_dev' => $hot !== null && $hot !== '',
            'vite_origin' => $hot,
            'broadcast' => config('broadcasting.default'),
            'reverb_host' => config('reverb.servers.reverb.host'),
            'reverb_port' => config('reverb.servers.reverb.port'),
            'queue' => config('queue.default'),
            'tunnel_admin' => (bool) config('tunnel.enabled'),
        ];
    }

    /**
     * @return array<int, array{label: string, url: string, description: string}>
     */
    public function getQuickLinks(): array
    {
        $base = rtrim((string) config('app.url'), '/');

        return [
            [
                'label' => 'Analytics dashboard',
                'url' => "{$base}/dashboard",
                'description' => 'KPIs, charts, Echo live order feed',
            ],
            [
                'label' => 'Shop',
                'url' => "{$base}/shop",
                'description' => 'Place a test order for broadcast demo',
            ],
            [
                'label' => 'Login',
                'url' => "{$base}/login",
                'description' => 'SSO buttons + email auth',
            ],
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('broadcastTest')
                ->label('Fire test broadcast')
                ->icon(Heroicon::OutlinedSignal)
                ->color('warning')
                ->requiresConfirmation()
                ->modalDescription('Dispatches NewOrderCreated on the private orders channel using the latest order.')
                ->action(function (): void {
                    $order = Order::query()->with('customer')->latest('id')->first();

                    if ($order === null) {
                        Notification::make()
                            ->title('No orders to broadcast')
                            ->body('Seed the database or place an order from /shop first.')
                            ->warning()
                            ->send();

                        return;
                    }

                    NewOrderCreated::dispatch($order);

                    Notification::make()
                        ->title('Broadcast sent')
                        ->body("NewOrderCreated for order #{$order->id} ({$order->formattedAmount()}).")
                        ->success()
                        ->send();
                }),
        ];
    }
}
