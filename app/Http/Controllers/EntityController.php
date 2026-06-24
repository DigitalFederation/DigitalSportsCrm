<?php

namespace App\Http\Controllers;

use App\Http\Requests\EntityCreateRequest;
use App\Models\Committee;
use App\Models\Country;
use App\Models\Sport;
use Domain\Entities\Actions\AssociateUserToEntityAction;
use Domain\Entities\Actions\CreateEntityAction;
use Domain\Entities\DataTransferObject\EntityData;
use Domain\Federations\Models\Federation;
use Domain\Users\Actions\CreateUserAction;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EntityController extends Controller
{
    public function create(): View
    {
        $federations = Federation::select('id', 'name')->orderBy('name')->get();
        $sports = Sport::query()->pluck('name', 'id');

        return view('web.public.entity.create', compact('federations', 'sports'));
    }

    public function store(
        EntityCreateRequest $request,
        CreateEntityAction $createEntityAction
    ): RedirectResponse {

        try {
            DB::beginTransaction();
            $validated = $request->validated();

            // Set country based on district
            $district = \Domain\Geographic\Models\District::find($validated['district_id']);
            if ($district) {
                $validated['country_id'] = $district->country_id;
            } else {
                // Fallback to configured default country
                $validated['country_id'] = config('app.default_country_id');
            }

            // Find federations based on selected district and entity type
            $federationIds = $this->determineFederations($validated);
            $validated['federation_id'] = $federationIds;

            $entity = $createEntityAction(EntityData::fromArray($validated));

            // Associate entity with committees based on selected types
            foreach ($validated['entity_types'] as $type) {
                $committeeCode = $type === 'sport' ? 'SPORT' : 'DIVING';
                $committee = Committee::where('code', $committeeCode)->first();
                if (! $committee) {
                    DB::rollBack();
                    Log::error("Committee not found: {$committeeCode}");

                    return back()->with('error', __('entity.committee_not_found', ['type' => $type]));
                }
                $entity->committees()->attach($committee->id);
            }

            // Create User
            try {
                $createUser = new CreateUserAction;
                $createUserResult = $createUser([
                    'name' => $request->user_email,
                    'email' => $request->user_email,
                    'password' => $request->password,
                    'password_confirmation' => $request->password_confirmation,
                    'role' => 'ENTITY',
                ]);
                $user = $createUserResult['user'];
            } catch (\Illuminate\Validation\ValidationException $e) {
                DB::rollBack();

                return back()
                    ->withInput()
                    ->withErrors($e->errors());
            }

            // Associate USER to Entity
            $associateUserToEntity = new AssociateUserToEntityAction;
            // Always assign entity-admin role
            $associateUserToEntity($createUserResult['user'], $entity, 'entity-admin');

            // Assign roles based on entity types
            foreach ($validated['entity_types'] as $type) {
                $roleCode = $type === 'sport' ? 'entity-sport' : 'entity-diving-services';
                $user->assignRole($roleCode);
            }

            // Notify entity to activate account
            $user->sendEmailVerificationNotification();

            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex->getCode() . ': ' . $ex->getMessage());

            return back()->with('error', 'Error creating this record, please contact the administrator.');
        }

        return redirect()->back()->with('success', 'Entity created with success. Please check your email to complete the verification process.');
    }

    /**
     * Determine federations based on selected district and entity type
     */
    private function determineFederations(array $validated): array
    {
        $federationIds = [];

        // Get the default federation or main parent federation dynamically
        $mainFederation = Federation::where('is_default_federation', true)->first();

        // If no default federation exists, get the main parent federation
        if (! $mainFederation) {
            $mainFederation = Federation::whereNull('parent_id')
                ->orderBy('id')
                ->first();
        }

        if ($mainFederation) {
            $federationIds[] = $mainFederation->id;
        }

        // Find local federations based on selected district
        if (! empty($validated['district_id'])) {
            // Method 1: Direct district-federation relationship
            $districtFederations = Federation::query()
                ->where('district_id', $validated['district_id'])
                ->where('is_local', true)
                ->pluck('id')
                ->toArray();

            if (! empty($districtFederations)) {
                $federationIds = array_merge($federationIds, $districtFederations);
            } else {
                // Method 2: If no direct district federations, find through zones
                $district = \Domain\Geographic\Models\District::with('zones')->find($validated['district_id']);
                if ($district && $district->zones->count() > 0) {
                    $zoneIds = $district->zones->pluck('id')->toArray();
                    $zoneFederations = Federation::query()
                        ->whereHas('zones', function ($query) use ($zoneIds) {
                            $query->whereIn('zones.id', $zoneIds);
                        })
                        ->where('is_local', true)
                        ->pluck('id')
                        ->toArray();

                    $federationIds = array_merge($federationIds, $zoneFederations);
                }
            }
        }

        // Find committee-based federations based on entity types
        foreach ($validated['entity_types'] as $type) {
            if ($type === 'sport') {
                // Find sport committee federations
                $sportCommittee = Committee::where('code', 'SPORT')->first();
                if ($sportCommittee) {
                    $sportFederations = Federation::query()
                        ->filterCommittee($sportCommittee->id)
                        ->where('is_local', false)
                        ->pluck('id')
                        ->toArray();

                    $federationIds = array_merge($federationIds, $sportFederations);
                }
            }

            if ($type === 'diving') {
                // Find diving committee federations
                $divingCommittee = Committee::where('code', 'DIVING')->first();
                if ($divingCommittee) {
                    $divingFederations = Federation::query()
                        ->filterCommittee($divingCommittee->id)
                        ->where('is_local', false)
                        ->pluck('id')
                        ->toArray();

                    $federationIds = array_merge($federationIds, $divingFederations);
                }
            }
        }

        // Remove duplicates
        return array_unique($federationIds);
    }
}
