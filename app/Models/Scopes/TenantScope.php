<?php

namespace App\Models\Scopes;

use App\Support\Tenancy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Constrains queries to the current tenant (design D1). When no tenant context
 * is resolved (e.g. console/seeding), the scope is a no-op so global operations
 * still work; row access on the API is additionally guarded by policies.
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $tenantId = app(Tenancy::class)->id();

        if ($tenantId === null) {
            return;
        }

        $builder->where($model->getTable().'.tenant_id', $tenantId);
    }
}
