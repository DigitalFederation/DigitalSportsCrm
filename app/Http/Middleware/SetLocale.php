<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $available = config('app.locales', []);

        // Priority: authenticated user's saved preference > session > app default.
        $locale = Auth::user()?->locale;

        if (! in_array($locale, $available, true)) {
            $locale = session('locale');
        }

        if (in_array($locale, $available, true)) {
            app()->setLocale($locale);
            session()->put('locale', $locale);
        }

        return $next($request);
    }
}
