<?php

namespace Domain\Users\Actions;

use App\Actions\Fortify\CreateNewUser;
use App\Models\Group;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class CreateUserAction
{
    /**
     * Roles authorized to bypass email verification when creating users
     */
    private const BYPASS_VERIFICATION_ROLES = [
        'admin',
        'federation-admin',
        'association-sport-admin',
        'association-scientific-admin',
        'association-admin',
        'association-territorial-admin',
    ];

    public function __invoke(array $user_data, $is_active = true)
    {
        // Create USER with random password
        $randomPassword = Str::random(8);

        if (empty($user_data['group_id'])) {
            $groupId = Group::where('code', $user_data['role'])->value('id');
        } else {
            $groupId = $user_data['group_id'];
        }

        $new_user = new CreateNewUser;
        $user = $new_user->create([
            'name' => ! empty($user_data['name']) ? $user_data['name'] : $user_data['email'],
            'email' => $user_data['email'],
            'password' => $user_data['password'] ?? $randomPassword,
            'password_confirmation' => $user_data['password_confirmation'] ?? $randomPassword,
            'group_id' => $groupId,
            'active' => $is_active,
        ]);

        // Optional bypass of MustVerifyEmail - only allowed for authorized roles
        if (isset($user_data['bypass_verification']) && $this->canBypassVerification()) {
            $user->markEmailAsVerified();
        }

        // Always save a token for password reset
        $token = Password::createToken($user);

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Check if the current authenticated user can bypass email verification
     */
    private function canBypassVerification(): bool
    {
        $currentUser = Auth::user();

        if (! $currentUser) {
            return false;
        }

        // Check if user has any of the authorized roles
        return $currentUser->hasAnyRole(self::BYPASS_VERIFICATION_ROLES);
    }
}
