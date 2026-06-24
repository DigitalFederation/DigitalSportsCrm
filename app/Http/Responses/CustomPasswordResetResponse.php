<?php

namespace App\Http\Responses;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\PasswordResetResponse;

class CustomPasswordResetResponse implements PasswordResetResponse
{
    public function __construct(protected string $status) {}

    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return new JsonResponse(['message' => trans($this->status)], 200);
        }

        // Auto-login the user after password reset
        $user = User::where('email', $request->email)->first();

        if ($user) {
            Auth::login($user);
            $user->load('group');

            $groupCode = $user->group?->code;

            $route = match ($groupCode) {
                'ADMIN' => route('admin.dashboard'),
                'FEDERATION' => route('federation.dashboard'),
                'ENTITY' => route('entity.dashboard'),
                'INDIVIDUAL' => route('individual.dashboard'),
                default => route('login'),
            };

            return redirect($route)->with('status', trans($this->status));
        }

        return redirect()->route('login')->with('status', trans($this->status));
    }
}
