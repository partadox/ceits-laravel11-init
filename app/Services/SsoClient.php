<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cookie;

class SsoClient
{
    protected string $apiUrl;
    protected string $apiKey;
    protected string $cookieName;
    protected string $frontendUrl;

    public function __construct()
    {
        $this->apiUrl = config('sso.api_url');
        $this->apiKey = config('sso.api_key');
        $this->cookieName = config('sso.cookie_name');
        $this->frontendUrl = config('sso.frontend_url');
    }

    /**
     * Verify JWT token dengan SSO backend
     */
    public function verifyToken(string $token): ?array
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey,
            ])->timeout(10)->post("{$this->apiUrl}/api/auth/verify", [
                'token' => $token,
            ]);

            if ($response->successful() && $response->json('success')) {
                return $response->json('user');
            }

            Log::warning('SSO token verification failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('SSO verification error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get SSO login URL
     */
    public function getLoginUrl(?string $returnUrl = null): string
    {
        $loginUrl = rtrim($this->frontendUrl, '/') . '/login';

        if ($returnUrl) {
            $loginUrl .= '?returnUrl=' . urlencode($returnUrl);
        }

        return $loginUrl;
    }

    /**
     * Get SSO logout URL
     */
    public function getLogoutUrl(): string
    {
        return rtrim($this->frontendUrl, '/') . '/login';
    }

    /**
     * Check if user has specific role
     */
    // public function hasRole(array|string $roles, ?array $user = null): bool
    // {
    //     if (!$user) {
    //         $user = session('sso_user');
    //     }

    //     if (!$user || !isset($user['role'])) {
    //         return false;
    //     }

    //     $roles = is_array($roles) ? $roles : [$roles];
    //     return in_array($user['role'], $roles);
    // }
}
