<?php

namespace App\Http\Controllers;

use App\Services\SsoClient;

class HomeController extends Controller
{
    protected SsoClient $ssoClient;

    public function __construct(SsoClient $ssoClient)
    {
        $this->ssoClient = $ssoClient;
    }

    public function index()
    {
        // Check if authenticated
        if (session()->has('sso_user')) {
            return redirect()->route('dashboard');
        }

        // Redirect to dashboard (will be caught by middleware and redirected to SSO properly)
        return redirect()->route('dashboard');
    }
}
