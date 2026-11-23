<?php

namespace App\Http\Middleware;

use App\Services\Family\FamilyAccessService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureFamilyPortalAccess
{
    public function __construct(
        private readonly FamilyAccessService $familyAccess
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('family');

        if (!$user || !$this->familyAccess->canAccess($user)) {
            Auth::guard('family')->logout();

            $request->session()->regenerateToken();

            return redirect()->route('family.login');
        }

        return $next($request);
    }
}
