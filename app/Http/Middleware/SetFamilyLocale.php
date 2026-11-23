<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetFamilyLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = ['id', 'en'];

        // Prioritaskan pilihan bahasa dari URL.
        if ($request->has('lang')) {
            $requestedLocale = $request->query('lang');

            if (in_array($requestedLocale, $supportedLocales, true)) {
                $request->session()->put('family_locale', $requestedLocale);
            }
        }

        // Gunakan bahasa dari session, default Indonesia.
        $locale = $request->session()->get('family_locale', 'id');

        if (!in_array($locale, $supportedLocales, true)) {
            $locale = 'id';
            $request->session()->put('family_locale', $locale);
        }

        App::setLocale($locale);

        return $next($request);
    }
}
