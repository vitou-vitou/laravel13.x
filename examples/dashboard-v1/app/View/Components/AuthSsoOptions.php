<?php

namespace App\View\Components;

use App\Services\SsoAuthenticator;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AuthSsoOptions extends Component
{
    /** @var list<string> */
    public array $enabledSso;

    public function __construct(SsoAuthenticator $ssoAuthenticator)
    {
        $this->enabledSso = $ssoAuthenticator->enabledProviders();
    }

    public function render(): View|Closure|string
    {
        return view('components.auth-sso-options');
    }
}
