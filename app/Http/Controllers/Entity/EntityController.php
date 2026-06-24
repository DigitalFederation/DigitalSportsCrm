<?php

namespace App\Http\Controllers\Entity;

use App\Http\Controllers\Controller;
use App\Models\Committee;
use App\Models\Country;
use Domain\Entities\Actions\EditEntityAction;
use Domain\Entities\DataTransferObject\EntityData;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class EntityController extends Controller
{
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(): View|RedirectResponse
    {
        $id = Auth::user()->entities()->first()->id;
        $entity = Entity::where(compact('id'))
            ->with([
                'federations',
                'committees:id',
            ])->firstOrFail();

        $federations = Federation::select('id', 'name')->orderBy('name')->get();
        $countries = Country::select('id', 'name')->orderBy('name')->get();
        $committees = Committee::select('id', 'name')->orderBy('name')->get();

        return view('web.entity.profile.edit', compact(
            'entity',
            'federations',
            'countries',
            'committees',
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        Request $request,
        int $id,
        EditEntityAction $editEntityAction
    ): RedirectResponse {
        // Check if user has required roles
        if (! Auth::user()->hasAnyRole(['entity-admin', 'entity-diving-services'])) {
            return redirect()->route('entity.dashboard')
                ->with('error', __('You do not have permission to update the entity profile.'));
        }

        Log::info('Entity update request data:', $request->all());

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'legal_name' => 'required|string|max:255',
            'legal_responsible_person' => 'nullable|string|max:255',
            'vat_number' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'country_id' => 'required|exists:country,id',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'public_description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'entity_background' => 'nullable|image|max:2048',
        ]);

        // Remove federation validation for profile editing
        if (Route::currentRouteName() === 'entity.profile.update') {
            unset($validated['federation_id']);
        }

        try {
            // Check if there's a background image and log information
            if ($request->hasFile('entity_background')) {
                Log::info('Background image file detected', [
                    'file_exists' => $request->file('entity_background')->isValid(),
                    'file_size' => $request->file('entity_background')->getSize(),
                    'file_mime' => $request->file('entity_background')->getMimeType(),
                    'file_name' => $request->file('entity_background')->getClientOriginalName(),
                ]);
            } else {
                Log::info('No background image in the request');
            }

            // Always fetch the entity first
            $entity = Entity::findOrFail($id);

            // Remove entity_background and logo from validated data since they're not part of EntityData
            $entityData = $validated;
            unset($entityData['entity_background']);
            unset($entityData['logo']);

            $edit = $editEntityAction(EntityData::fromArray($entityData), $id);

            if (empty($edit)) {
                Log::error('Error finding the record: ' . json_encode($validated));

                return back()->with('error', __('Error updating record. Form data is invalid.'));
            }

            // Handle background image upload immediately after entity is found
            if ($request->hasFile('entity_background')) {
                try {
                    // Clear existing media first to replace it
                    $entity->clearMediaCollection('entity-background');
                    $entity->addMediaFromRequest('entity_background')
                        ->toMediaCollection('entity-background');
                    Log::info('Background image successfully uploaded');
                } catch (Exception $ex) {
                    Log::error('Error uploading background image: ' . $ex->getMessage(), [
                        'exception' => $ex,
                        'trace' => $ex->getTraceAsString(),
                    ]);
                    // Don't fail the entire request if just the image upload fails
                }
            }

            // Handle logo upload
            if ($request->hasFile('logo')) {
                try {
                    // Clear existing media first to replace it
                    $entity->clearMediaCollection('profile');
                    $entity->addMediaFromRequest('logo')
                        ->toMediaCollection('profile');
                    Log::info('Entity logo successfully uploaded');
                } catch (Exception $ex) {
                    Log::error('Error uploading entity logo: ' . $ex->getMessage(), [
                        'exception' => $ex,
                        'trace' => $ex->getTraceAsString(),
                    ]);
                    // Don't fail the entire request if just the image upload fails
                }
            }

        } catch (Exception $ex) {
            Log::error($ex->getCode() . ': ' . $ex->getMessage());

            return back()->with('error', __('Error updating this record: ') . $ex->getMessage());
        }

        return redirect(route('entity.profile.edit'))->with('success', __('Record updated with success.'));
    }

    /**
     * Remove the federation association for the specified entity.
     */
    public function removeFederation(int $entityId): RedirectResponse
    {
        $entity = Entity::findOrFail($entityId);

        // Perform necessary checks and validations
        if ($entity->federations->isNotEmpty()) {
            // Detach the federation associations
            $entity->federations()->detach();

            return redirect(route('entity.profile.edit'))->with('success', __('Federation association removed successfully.'));
        }

        return back()->with('error', __('No federation association found.'));
    }
}
