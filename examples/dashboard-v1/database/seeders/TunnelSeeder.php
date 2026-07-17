<?php

namespace Database\Seeders;

use App\Models\Tunnel;
use Illuminate\Database\Seeder;

class TunnelSeeder extends Seeder
{
    public function run(): void
    {
        if (! config('tunnel.enabled')) {
            return;
        }

        $herdHost = (string) config('tunnel.default_herd_host', 'dashboard-v1.test');
        $envDomain = env('NGROK_DEV_DOMAIN');
        $primaryDomain = is_string($envDomain) && $envDomain !== ''
            ? Tunnel::normalizeDomain($envDomain)
            : 'demo-primary.ngrok-free.dev';

        $primary = Tunnel::query()->where('domain', $primaryDomain)->first()
            ?? Tunnel::query()->firstOrNew(['name' => 'Default — local SSO']);

        $primary->fill([
            'name' => $primary->exists && $primary->name !== 'Default — local SSO'
                ? $primary->name
                : 'Default — local SSO',
            'domain' => $primaryDomain,
            'herd_host' => $herdHost,
            'is_active' => true,
        ])->save();

        $demos = [
            ['name' => 'Demo — team Alpha', 'domain' => 'team-alpha.ngrok-free.dev'],
            ['name' => 'Demo — team Beta', 'domain' => 'team-beta.ngrok-free.dev'],
            ['name' => 'Demo — staging', 'domain' => 'staging-dash.ngrok-free.dev'],
            ['name' => 'Demo — spare slot', 'domain' => 'spare-tunnel.ngrok-free.dev'],
        ];

        foreach ($demos as $demo) {
            $this->upsertDemoProfile($demo['name'], $demo['domain'], $herdHost);
        }

        Tunnel::query()->whereKeyNot($primary->id)->update(['is_active' => false]);
        $primary->update(['is_active' => true]);
    }

    private function upsertDemoProfile(string $name, string $domain, string $herdHost): void
    {
        $tunnel = Tunnel::query()->firstOrNew(['name' => $name]);

        $domainTaken = Tunnel::query()
            ->where('domain', $domain)
            ->when($tunnel->exists, fn ($query) => $query->whereKeyNot($tunnel->id))
            ->exists();

        if ($domainTaken && ! $tunnel->exists) {
            return;
        }

        $tunnel->fill([
            'domain' => $domain,
            'herd_host' => $herdHost,
            'is_active' => false,
        ])->save();
    }
}
