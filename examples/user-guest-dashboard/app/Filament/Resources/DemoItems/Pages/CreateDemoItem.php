<?php

namespace App\Filament\Resources\DemoItems\Pages;

use App\Filament\Resources\DemoItems\DemoItemResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateDemoItem extends CreateRecord
{
    protected static string $resource = DemoItemResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['guest_id'] = Filament::auth()->id();

        return $data;
    }
}
