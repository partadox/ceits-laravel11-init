<?php

namespace App\Http\Controllers;

use App\Services\SsoClient;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected SsoClient $ssoClient;

    public function __construct(SsoClient $ssoClient)
    {
        $this->ssoClient = $ssoClient;
    }

    /**
     * Handle SSO callback with token
     * This route is NOT protected by middleware
     */
    public function callback(Request $request)
    {
        \Log::info('=== AuthController::callback START ===');
        \Log::info('URL: ' . $request->fullUrl());
        \Log::info('Has token in query: ' . ($request->query('token') ? 'YES' : 'NO'));
        \Log::info('Session ID: ' . session()->getId());

        $token = $request->query('token');

        // Get intended URL from session, default to dashboard
        $intendedUrl = session('intended_url', '/dashboard');
        \Log::info('Intended URL from session: ' . $intendedUrl);

        // If no token, redirect to SSO login
        if (!$token) {
            \Log::error('No token in query parameter');
            return redirect($this->ssoClient->getLoginUrl(url('/auth/callback')));
        }

        // Verify token with SSO backend
        \Log::info('Verifying token with SSO backend...');
        $userData = $this->ssoClient->verifyToken($token);

        // If token invalid, redirect to SSO login
        if (!$userData) {
            \Log::error('Token verification failed');
            return redirect($this->ssoClient->getLoginUrl(url('/auth/callback')));
        }

        \Log::info('Token verified successfully');
        \Log::info('User data: ' . json_encode($userData));

        // Token valid, save to session and cookie
        session(['sso_user' => $userData]);
        session()->forget('intended_url'); // Clear intended URL
        session()->save(); // Force save

        \Log::info('Session saved with sso_user');
        \Log::info('Redirecting to: ' . $intendedUrl);
        \Log::info('=== AuthController::callback END ===');

        // Redirect to intended URL (clean, without token)
        return redirect($intendedUrl)->withCookie(
            cookie('ceits_prisma_token', $token, 60 * 24 * 7)
        );
    }

    public function logout(Request $request)
    {
        // Clear session
        $request->session()->forget('sso_user');
        $request->session()->flush();

        // Clear Laravel's cookie
        cookie()->queue(cookie()->forget('ceits_prisma_token'));

        // Redirect to SSO logout (which will clear ceits_sso_token cookie)
        return redirect($this->ssoClient->getLogoutUrl());
    }
}
