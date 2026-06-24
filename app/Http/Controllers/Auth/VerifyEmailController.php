<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    public function verify(Request $request)
    {

        $id = $request->route('id');
        $hash = $request->route('hash');

        $user = User::find($id);

        if (! $user || ! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect(route('login'))->with('error', 'Invalid verification link.');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect(route('login'))->with('info', 'Your email is already verified. You can log in.');
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return redirect(route('login'))->with('success', 'Your email has been verified! You can now login.');
    }
}
