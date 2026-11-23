<?php

namespace App\Http\Controllers\Family;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Family\FamilyAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        private readonly FamilyAccessService $familyAccess
    ) {
    }

    public function showLogin(): View|RedirectResponse
    {
        $user = Auth::guard('family')->user();

        if ($user && $this->familyAccess->canAccess($user)) {
            return redirect()->route('family.dashboard');
        }

        return view('family.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $authenticated = Auth::guard('family')->attempt(
            [
                'email' => $credentials['email'],
                'password' => $credentials['password'],
            ],
            $request->boolean('remember')
        );

        if (!$authenticated) {
            return $this->loginFailed();
        }

        $user = Auth::guard('family')->user();

        if (!$user || !$this->familyAccess->canAccess($user)) {
            Auth::guard('family')->logout();

            $request->session()->regenerateToken();

            return $this->loginFailed();
        }

     $request->session()->regenerate();

        $request->session()->forget('url.intended');

        return redirect()->route('family.dashboard');
            }

    private function loginFailed(): RedirectResponse
    {
        return back()
            ->withErrors([
                'login' => session('family_locale') === 'en'
                    ? 'The email or password you entered is incorrect.'
                    : 'Email atau kata sandi yang Anda masukkan salah.',
            ])
            ->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('family')->logout();

        $request->session()->regenerateToken();

        return redirect()->route('family.login');
    }
}
