<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ImpersonateController extends Controller
{
    public function start(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Security check - only Admins can impersonate
        if (! $this->canImpersonate()) {
            abort(403, 'Unauthorized action.');
        }

        // Additional checks for target user
        if (! in_array($user->group->code, ['ENTITY', 'FEDERATION', 'INDIVIDUAL'])) {
            return redirect()->back()->with('error', 'Can only impersonate ENTITY, FEDERATION, or INDIVIDUAL users');
        }

        if (! $user->active) {
            return redirect()->back()->with('error', 'User is not active');
        }

        // Check for required relationships based on user group
        if ($user->group->code === 'ENTITY' && empty($user->entities()->first())) {
            return redirect()->back()->with('error', 'Entity user has no associated entity');
        } elseif ($user->group->code === 'FEDERATION' && empty($user->federations()->first())) {
            return redirect()->back()->with('error', 'Federation user has no associated federation');
        } elseif ($user->group->code === 'INDIVIDUAL' && empty($user->individual)) {
            return redirect()->back()->with('error', 'Individual user has no associated individual profile');
        }

        // Log the impersonation for audit purposes
        Log::info("Admin {$request->user()->id} impersonating user {$user->id} ({$user->group->code})");

        // Store original user
        $request->session()->put('impersonate_original', Auth::id());
        // Set impersonated user
        $request->session()->put('impersonate', $user->id);

        // Redirect based on impersonated user's group
        return $this->redirectToUserDashboard($user);
    }

    public function stop(Request $request)
    {
        // Get the original user ID
        $originalId = $request->session()->get('impersonate_original');

        // Log the end of impersonation
        Log::info("Admin {$originalId} stopped impersonating user {$request->session()->get('impersonate')}");

        // Clear impersonation data
        $request->session()->forget('impersonate');
        $request->session()->forget('impersonate_original');

        // Login as original user
        Auth::loginUsingId($originalId);

        return redirect()->route('admin.dashboard');
    }

    private function canImpersonate()
    {
        return Auth::user()->group->code === 'ADMIN' &&
            Auth::user()->can('impersonate users');
    }

    private function redirectToUserDashboard(User $user)
    {
        $group = strtolower($user->group->code);

        return redirect()->route("{$group}.dashboard");
    }
}
