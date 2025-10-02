<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\SsoClient;
use Symfony\Component\HttpFoundation\Response;

class SsoRole
{
    protected SsoClient $ssoClient;

    public function __construct(SsoClient $ssoClient)
    {
        $this->ssoClient = $ssoClient;
    }

    /**
     * Handle an incoming request.
     *
     * @param array<string> $roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = session('sso_user');

        if (!$user) {
            return redirect($this->ssoClient->getLoginUrl($request->fullUrl()));
        }

        if (!$this->ssoClient->hasRole($roles, $user)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
