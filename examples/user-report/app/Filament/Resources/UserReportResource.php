<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\UserReportResource\Pages\ListUserReports;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class UserReportResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'User Report';

    protected static ?string $modelLabel = 'User';

    // ---------------------------------------------------------------------------
    // Read-only: disable all write actions
    // ---------------------------------------------------------------------------

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    // ---------------------------------------------------------------------------
    // Table
    // ---------------------------------------------------------------------------

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->paginated([20, 50, 100])
            ->striped()
            ->filtersLayout(FiltersLayout::AboveContent)
            ->columns([
                // 1. Avatar
                ImageColumn::make('avatar')
                    ->circular()
                    ->size(40),

                // 2. Username
                TextColumn::make('username')
                    ->searchable()
                    ->sortable(),

                // 3. Email
                TextColumn::make('email')
                    ->searchable()
                    ->copyable(),

                // 4. Country
                TextColumn::make('country')
                    ->sortable(),

                // 5. City
                TextColumn::make('city')
                    ->sortable(),

                // 6. Device type — badge with semantic colours
                BadgeColumn::make('device_type')
                    ->colors([
                        'success' => 'mobile',
                        'primary' => 'desktop',
                        'warning' => 'tablet',
                    ]),

                // 7. Signup source
                TextColumn::make('signup_source')
                    ->sortable(),

                // 8. Computed user status (Active / Inactive)
                BadgeColumn::make('user_status')
                    ->getStateUsing(fn (User $record): string => $record->user_status)
                    ->colors([
                        'success' => 'Active',
                        'danger'  => 'Inactive',
                    ]),

                // 9. Distance — only meaningful when geo filter is active; hidden by default
                TextColumn::make('distance_km')
                    ->label('Distance (km)')
                    ->numeric(decimalPlaces: 1)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                // 10. Last login
                TextColumn::make('last_login_at')
                    ->label('Last Login')
                    ->since()
                    ->sortable(),

                // 11. Joined
                TextColumn::make('created_at')
                    ->label('Joined')
                    ->date()
                    ->sortable(),
            ])

            // ---------------------------------------------------------------
            // Filters
            // ---------------------------------------------------------------
            ->filters([
                // 1. Country
                SelectFilter::make('country')
                    ->options(
                        DB::table('users')
                            ->distinct()
                            ->orderBy('country')
                            ->pluck('country', 'country')
                            ->filter()
                            ->toArray()
                    )
                    ->placeholder('All countries')
                    ->modifyQueryUsing(function (Builder $query, array $data): Builder {
                        return $query->byCountry($data['value'] ?? null);
                    }),

                // 2. City (free-text)
                Filter::make('city')
                    ->form([
                        TextInput::make('city')
                            ->placeholder('e.g. Paris')
                            ->label('City'),
                    ])
                    ->modifyQueryUsing(function (Builder $query, array $data): Builder {
                        return $query->byCity($data['city'] ?? null);
                    }),

                // 3. Device type
                SelectFilter::make('device_type')
                    ->options(
                        collect(User::DEVICE_TYPES)
                            ->mapWithKeys(fn (string $v): array => [$v => ucfirst($v)])
                            ->toArray()
                    )
                    ->placeholder('All devices')
                    ->modifyQueryUsing(function (Builder $query, array $data): Builder {
                        return $query->byDeviceType($data['value'] ?? null);
                    }),

                // 4. Signup source
                SelectFilter::make('signup_source')
                    ->options(
                        collect(User::SIGNUP_SOURCES)
                            ->mapWithKeys(fn (string $v): array => [$v => ucfirst($v)])
                            ->toArray()
                    )
                    ->placeholder('All sources')
                    ->modifyQueryUsing(function (Builder $query, array $data): Builder {
                        return $query->bySignupSource($data['value'] ?? null);
                    }),

                // 5. Active / Inactive
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ])
                    ->placeholder('Any status')
                    ->modifyQueryUsing(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if ($value === null || $value === '') {
                            return $query;
                        }

                        return $query->active((bool) $value);
                    }),

                // 6. Has avatar
                SelectFilter::make('has_image')
                    ->label('Avatar')
                    ->options([
                        1 => 'Has Avatar',
                        0 => 'No Avatar',
                    ])
                    ->placeholder('Any')
                    ->modifyQueryUsing(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if ($value === null || $value === '') {
                            return $query;
                        }

                        return $query->hasAvatar((bool) $value);
                    }),

                // 7. Keyword search
                Filter::make('keyword')
                    ->form([
                        TextInput::make('keyword')
                            ->placeholder('Search username, email, city, country…')
                            ->label('Keyword'),
                    ])
                    ->modifyQueryUsing(function (Builder $query, array $data): Builder {
                        return $query->keyword($data['keyword'] ?? null);
                    }),

                // 8. Period / date range
                Filter::make('period')
                    ->form([
                        Select::make('period')
                            ->options([
                                'day'    => 'Today',
                                'week'   => 'This week',
                                'month'  => 'This month',
                                'year'   => 'This year',
                                'custom' => 'Custom range',
                            ])
                            ->placeholder('Any period')
                            ->live(),

                        DatePicker::make('start')
                            ->label('From')
                            ->visible(fn (callable $get): bool => $get('period') === 'custom'),

                        DatePicker::make('end')
                            ->label('To')
                            ->visible(fn (callable $get): bool => $get('period') === 'custom'),
                    ])
                    ->modifyQueryUsing(function (Builder $query, array $data): Builder {
                        return $query->period(
                            $data['period'] ?? null,
                            $data['start']  ?? null,
                            $data['end']    ?? null,
                        );
                    }),

                // 9. Geo / radius filter
                Filter::make('geo')
                    ->label('Within radius')
                    ->form([
                        TextInput::make('center_lat')
                            ->label('Centre latitude')
                            ->numeric()
                            ->placeholder('e.g. 48.8566'),

                        TextInput::make('center_long')
                            ->label('Centre longitude')
                            ->numeric()
                            ->placeholder('e.g. 2.3522'),

                        TextInput::make('radius_km')
                            ->label('Radius (km)')
                            ->numeric()
                            ->placeholder('e.g. 50'),
                    ])
                    ->modifyQueryUsing(function (Builder $query, array $data): Builder {
                        $lat    = isset($data['center_lat'])  && $data['center_lat']  !== '' ? (float) $data['center_lat']  : null;
                        $lng    = isset($data['center_long']) && $data['center_long'] !== '' ? (float) $data['center_long'] : null;
                        $radius = isset($data['radius_km'])   && $data['radius_km']   !== '' ? (float) $data['radius_km']  : null;

                        if ($lat === null || $lng === null || $radius === null) {
                            return $query;
                        }

                        // Add the Haversine distance as a computed column so the
                        // distance_km table column can render and sort it.
                        $query->selectRaw('*, (6371 * ACOS(COS(RADIANS(?)) * COS(RADIANS(geo_lat)) * COS(RADIANS(geo_long) - RADIANS(?)) + SIN(RADIANS(?)) * SIN(RADIANS(geo_lat)))) as distance_km', [
                            $lat, $lng, $lat,
                        ]);

                        return $query->withinRadius($lat, $lng, $radius);
                    }),
            ]);
    }

    // ---------------------------------------------------------------------------
    // Pages — list only (read-only resource)
    // ---------------------------------------------------------------------------

    public static function getPages(): array
    {
        return [
            'index' => ListUserReports::route('/'),
        ];
    }
}
