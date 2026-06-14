<?php

namespace App\Models\Scopes;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class VendorTenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (! app()->runningInConsole() && auth()->check() && auth()->user()->isVendor()) {
            $vendorId = Vendor::query()
                ->withoutGlobalScope(self::class)
                ->where('user_id', auth()->id())
                ->value('id');

            $table = $model->getTable();

            if ($vendorId !== null && $table !== 'vendors') {
                $builder->where($table.'.vendor_id', $vendorId);
            }

            if ($vendorId !== null && $table === 'vendors') {
                $builder->where($table.'.id', $vendorId);
            }
        }
    }
}
