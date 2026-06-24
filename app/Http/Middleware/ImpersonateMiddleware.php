<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ImpersonateMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // First, prevent any redirect loops
        if ($request->path() === 'impersonate-switch') {
            // Clear any problematic cookies
            return redirect()->route('login')
                ->cookie(cookie()->forget('impersonate_temp'));
        }

        // Handle regular impersonation
        if ($request->session()->has('impersonate')) {
            $impersonatedUserId = $request->session()->get('impersonate');
            $originalUserId = $request->session()->get('impersonate_original');

            // Only continue if not already impersonating correctly
            if (! Auth::check() || Auth::id() !== $impersonatedUserId) {
                $user = User::find($impersonatedUserId);

                if (! $user) {
                    $request->session()->forget(['impersonate', 'impersonate_original']);

                    return redirect()->route('login');
                }

                Log::info('Impersonation attempt', [
                    'user_id' => $user->id,
                    'auth_id' => Auth::id(),
                ]);

                // Reset session completely but preserve impersonation data
                try {
                    // Create a new session
                    session()->regenerate(true);

                    // Directly manipulate session data
                    $sessionKey = 'login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d';
                    session()->put($sessionKey, $user->getAuthIdentifier());
                    session()->put('impersonate', $impersonatedUserId);
                    session()->put('impersonate_original', $originalUserId);
                    session()->save();

                    // Force Auth to recognize our change
                    Auth::setUser($user);

                    Log::info('Direct session auth set', [
                        'user_id' => $user->id,
                        'new_auth_id' => Auth::id(),
                        'session_id' => session()->getId(),
                    ]);

                    // Return to same page to refresh state
                    return redirect()->to($request->fullUrl());
                } catch (\Exception $e) {
                    Log::error('Impersonation error', [
                        'message' => $e->getMessage(),
                        'user_id' => $user->id,
                    ]);

                    session()->forget(['impersonate', 'impersonate_original']);

                    return redirect()->route('login');
                }
            }
        }

        return $next($request);
    }
}
