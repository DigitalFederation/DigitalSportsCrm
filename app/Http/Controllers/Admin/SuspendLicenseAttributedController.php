<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Domain\Licenses\Actions\SuspendLicenseAttributedAction;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\Scopes\ExcludeInternationalScope;
use Domain\Users\Actions\RevokeUserRolesForSuspendedLicenseAction;
use Illuminate\Http\Request;

class SuspendLicenseAttributedController extends Controller
{
    public function store(Request $request)
    {
        $license = LicenseAttributed::withoutGlobalScope(ExcludeInternationalScope::class)
            ->findOrFail($request->input('license_id'));

        try {
            $suspendAction = new SuspendLicenseAttributedAction;
            $suspendAction($license);

            // Now revoke the roles associated with this suspended license
            $user = $license->owner->user()->first();
            if ($user) {
                $revokeAction = new RevokeUserRolesForSuspendedLicenseAction;
                $revokeAction->execute($user, $license);
            }
        } catch (\Exception $e) {
            // Handle the exception, e.g., return with an error message
            return back()->with('error', $e->getMessage());
        }

        // redirect back
        return redirect(route('admin.license-attributed.show', $license->id))->with('success', 'License suspended with success.');
    }
}
