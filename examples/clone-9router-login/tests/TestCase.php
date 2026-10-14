<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null): TestResponse
    {
        $this->flushJwtGuard();

        return parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);
    }

    private function flushJwtGuard(): void
    {
        if (! $this->app) {
            return;
        }

        $guard = Auth::guard('api');
        $guard->forgetUser();
        $guard->unsetToken();
    }
}
