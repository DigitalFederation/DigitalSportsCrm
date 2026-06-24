<?php

namespace App\Http\Controllers\Federation;

use App\Http\Controllers\Controller;
use App\Http\Requests\FederationProfileEditRequest;
use Domain\Federations\Actions\EditFederationAction;
use Domain\Federations\DataTransferObject\FederationData;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class FederationController extends Controller
{
    public function edit(): View
    {
        $federation = auth()->user()->federations()->first();

        return view('web.federation.federation.edit', compact('federation'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        FederationProfileEditRequest $request,
        EditFederationAction $editFederation
    ): RedirectResponse {

        $federation = auth()->user()->federations()->first();
        $federationId = $federation->id;

        try {
            // Retrieve non-editable data from the database
            $nonEditableData = [
                'country_id' => $federation->country_id,
                'name' => $federation->name,
                'member_code' => $federation->member_code,
            ];
            // Merge editable and non-editable data
            $data = array_merge($request->validated(), $nonEditableData);

            // Run the edit action
            $editFederation(FederationData::fromArray($data), $federationId);

            return redirect(route('federation.profile.edit', $federationId))->with('success', __('federation.profile_updated_success'));
        } catch (Exception $ex) {
            Log::error($ex->getCode() . ': ' . $ex->getMessage());

            return back()->with('error', __('federation.profile_update_error'));
        }
    }
}
