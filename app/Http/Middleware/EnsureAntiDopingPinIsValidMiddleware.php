<?php

namespace App\Http\Middleware;

use Closure;
use Domain\EvtEvents\Models\EventPin;
use Illuminate\Http\Request;

class EnsureAntiDopingPinIsValidMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $pin = $request->query('pin', $request->session()->get('event_pin'));

        if (! $pin || ! EventPin::where('pin', $pin)->exists()) {
            return redirect()->route('public.anti-doping.pin');
        }

        $request->session()->put('event_pin', $pin);

        return $next($request);
    }
}
