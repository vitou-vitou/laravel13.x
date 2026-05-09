<?php

namespace App\Filament\Resources\DemoItems\Pages;

use App\Filament\Resources\DemoItems\DemoItemResource;
use Filament\Resources\Pages\EditRecord;

class EditDemoItem extends EditRecord
{
    protected static string $resource = DemoItemResource::class;
}
