<?php

namespace App\Http\Middleware;

use Closure;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\Scopes\ExcludeInternationalScope;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CheckEntityCanInvite
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $committee_code): Response
    {
        $entity = Auth()->user()->entities->first();

        if (! $entity) {
            throw new HttpException(403, 'No entity found for the current user.');
        }

        // Normalize committee code to lowercase (database stores lowercase codes)
        $committee_code = strtolower($committee_code);

        // Check for active license, including international licenses (international)
        // We need to bypass ExcludeInternationalScope as diving/scientific licenses are international
        $hasRequiredLicense = LicenseAttributed::withoutGlobalScope(ExcludeInternationalScope::class)
            ->where('model_type', 'entity')
            ->where('model_id', $entity->id)
            ->where('status_class', ActiveLicenseAttributedState::class)
            ->whereHas('license', function (Builder $query) use ($committee_code) {
                $query->withoutGlobalScope(ExcludeInternationalScope::class)
                    ->whereHas('committee', function (Builder $query) use ($committee_code) {
                        $query->where('code', $committee_code);
                    });
            })->exists();

        if (! $hasRequiredLicense) {
            $committeeName = ucfirst($committee_code);

            // Check if they have an inactive license
            $hasInactiveLicense = LicenseAttributed::withoutGlobalScope(ExcludeInternationalScope::class)
                ->where('model_type', 'entity')
                ->where('model_id', $entity->id)
                ->where('status_class', '!=', ActiveLicenseAttributedState::class)
                ->whereHas('license', function (Builder $query) use ($committee_code) {
                    $query->withoutGlobalScope(ExcludeInternationalScope::class)
                        ->whereHas('committee', function (Builder $query) use ($committee_code) {
                            $query->where('code', $committee_code);
                        });
                })->exists();

            if ($hasInactiveLicense) {
                $message = __('licenses.entity_has_inactive_license', ['committee' => $committeeName]);
            } else {
                $message = __('licenses.entity_needs_active_license', ['committee' => $committeeName]);
            }

            // If it's an AJAX request, return JSON
            if ($request->expectsJson()) {
                return response()->json(['error' => $message], 403);
            }

            // Otherwise, redirect back with error message
            return redirect()->back()->with('error', $message);
        }

        return $next($request);
    }
}
