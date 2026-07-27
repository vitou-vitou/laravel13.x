<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

/**
 * Holds the current tenant context for row-level multi-tenancy (design D1).
 *
 * Resolution order: an explicitly set tenant id (e.g. via middleware, console,
 * or tests) takes precedence; otherwise it falls back to the authenticated
 * user's tenant_id.
 */
class Tenancy
{
    protected ?int $tenantId = null;

    protected bool $explicit = false;

    public function set(?int $tenantId): void
    {
        $this->tenantId = $tenantId;
        $this->explicit = true;
    }

    public function forget(): void
    {
        $this->tenantId = null;
        $this->explicit = false;
    }

    public function id(): ?int
    {
        if ($this->explicit) {
            return $this->tenantId;
        }

        return Auth::user()?->tenant_id;
    }

    public function has(): bool
    {
        return $this->id() !== null;
    }
}
