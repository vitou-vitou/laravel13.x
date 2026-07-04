<?php

namespace App\Services;

use App\Models\PlatformSetting;

class CommissionService
{
    public function defaultBps(): int
    {
        return PlatformSetting::query()->value('default_commission_bps') ?? 1000;
    }

    public function updateDefaultBps(int $bps): void
    {
        $setting = PlatformSetting::query()->first();

        if ($setting === null) {
            PlatformSetting::query()->create(['default_commission_bps' => $bps]);

            return;
        }

        $setting->update(['default_commission_bps' => $bps]);
    }
}
