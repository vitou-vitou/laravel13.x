<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\ClientRepository;

abstract class TestCase extends BaseTestCase
{
    /**
     * Ensure a Passport personal access client exists once the schema is
     * migrated, so token issuance (design D2) works in feature tests. No-ops
     * for unit tests that never migrate the oauth tables.
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('oauth_clients')) {
            return;
        }

        $clients = app(ClientRepository::class);

        try {
            $clients->personalAccessClient(config('auth.guards.api.provider'));
        } catch (\RuntimeException) {
            $clients->createPersonalAccessGrantClient('Testing Personal Access Client');
        }
    }
}
