<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subdomain',
        'config',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }

    public function slaForType(string $type): int
    {
        $defaults = ['collision' => 7, 'theft' => 14, 'injury' => 10];
        return data_get($this->config, "sla.{$type}", $defaults[$type] ?? 7);
    }

    public function requiredDocsForType(string $type): array
    {
        $defaults = [
            'collision' => ['driver_license', 'vehicle_registration', 'police_report', 'photo'],
            'theft' => ['driver_license', 'vehicle_registration', 'police_report'],
            'injury' => ['driver_license', 'vehicle_registration', 'police_report', 'photo'],
        ];
        return data_get($this->config, "required_docs.{$type}", $defaults[$type] ?? []);
    }
}
