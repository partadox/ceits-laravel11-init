<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\SsoClient;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cookie;

class SsoAuth
{
    protected SsoClient $ssoClient;

    public function __construct(SsoClient $ssoClient)
    {
        $this->ssoClient = $ssoClient;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        \Log::info('=== SsoAuth Middleware START ===');
        \Log::info('URL: ' . $request->fullUrl());
        \Log::info('Method: ' . $request->method());
        \Log::info('Session ID: ' . session()->getId());
        \Log::info('Has sso_user in session: ' . (session()->has('sso_user') ? 'YES' : 'NO'));
        \Log::info('Cookie ceits_prisma_token: ' . ($request->cookie('ceits_prisma_token') ? 'EXISTS' : 'NOT FOUND'));

        // 1. Cek apakah sudah ada session user
        if (session()->has('sso_user')) {
            \Log::info('User authenticated from session, continuing...');
            view()->share('user', session('sso_user'));
            return $next($request);
        }

        // 2. Cek token dari cookie Laravel
        $token = $request->cookie('ceits_prisma_token');

        // 3. Jika ada token di cookie, verify
        if ($token) {
            \Log::info('Token found in cookie, verifying...');
            $userData = $this->ssoClient->verifyToken($token);

            if ($userData) {
                \Log::info('Token valid, restoring session');
                session(['sso_user' => $userData]);
                view()->share('user', $userData);
                return $next($request);
            }

            \Log::warning('Token invalid or expired');
            Cookie::queue(Cookie::forget('ceits_prisma_token'));
        }

        // 4. Tidak ada session & cookie, redirect ke SSO login
        \Log::info('No valid session or cookie, redirecting to SSO login');
        \Log::info('Saving intended URL: ' . $request->fullUrl());
        session(['intended_url' => $request->fullUrl()]);
        session()->save(); // Force save

        $callbackUrl = url('/auth/callback');
        $loginUrl = $this->ssoClient->getLoginUrl($callbackUrl);
        \Log::info('Redirecting to: ' . $loginUrl);
        \Log::info('=== SsoAuth Middleware END (REDIRECT) ===');

        return redirect($loginUrl);
    }
}
