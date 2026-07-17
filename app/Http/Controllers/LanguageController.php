<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class LanguageController extends Controller
{
    public function switchLang(string $locale): RedirectResponse
    {
        if (! in_array($locale, config('app.locales', []), true)) {
            return redirect()->back();
        }

        session()->put('locale', $locale);

        if (Auth::check()) {
            Auth::user()->forceFill(['locale' => $locale])->save();
        }

        app()->setLocale($locale);

        return redirect()->back();
    }
}
