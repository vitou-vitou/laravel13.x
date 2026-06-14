<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class VendorTenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (! app()->runningInConsole() && auth()->check() && auth()->user()->isVendor()) {
            $vendorId = auth()->user()->vendor?->id;

            if ($vendorId !== null && $model->getTable() !== 'vendors') {
                $builder->where($model->getTable().'.vendor_id', $vendorId);
            }

            if ($vendorId !== null && $model->getTable() === 'vendors') {
                $builder->where($model->getTable().'.id', $vendorId);
            }
        }
    }
}
