<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserCheckIfEmptyRelations
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            if (! $this->userCheckGroup(Auth::user())) {
                throw new HttpException(403, "This user doesn't have any relation with any item.");
            }
        }

        return $next($request);
    }

    public function userCheckGroup($user): bool
    {
        $groupCode = $user->group?->code;

        return match ($groupCode) {
            'INDIVIDUAL' => $user->individuals()->exists(),
            'ENTITY' => $user->entities()->exists(),
            'FEDERATION' => $user->federations()->exists(),
            'ADMIN' => true,
            default => false,
        };
    }
}
