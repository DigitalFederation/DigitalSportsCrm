<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePublicIndividualRequest;
use App\Models\Committee;
use App\Models\Country;
use App\Models\Sport;
use App\Support\DefaultCountryResolver;
use Domain\Entities\Models\Entity;
use Domain\Geographic\Enums\TerritorySelection;
use Domain\Geographic\Models\Zone;
use Domain\Individuals\Actions\CreateIndividualAction;
use Domain\Individuals\Actions\CreateIndividualEntityAction;
use Domain\Individuals\DataTransferObject\IndividualData;
use Domain\Memberships\Services\MemberNumberService;
use Domain\Users\Actions\CreateUserAction;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class IndividualController extends Controller
{
    public function create(DefaultCountryResolver $defaultCountryResolver): View
    {
        // List of sports
        $sports = Sport::query()->orderBy('name')->pluck('name', 'id');
        // List of Committees
        $committees = Committee::query()->orderBy('name')->pluck('name', 'id');
        // Countries - still needed for individual nationality
        $countries = Country::query()->orderBy('name')->pluck('name', 'id');
        $defaultCountryId = $defaultCountryResolver->resolve()->id;
        // List of active entities (entities with active federation status)
        $entities = Entity::whereHas('federations', function ($query) {
            $query->where('entity_federation.status_class', \Domain\Entities\States\ActiveEntityFederationState::class);
        })
            ->orderBy('name')
            ->pluck('name', 'id');

        return view('web.public.individual.create', compact(
            'countries', 
            'sports', 
            'committees', 
            'defaultCountryId',
            'entities'));
    }

    /**
     * Return only zones that are actually linked to districts of the selected country.
     *
     * The current schema has no country_id on zones, so the country/zone relationship
     * is derived through districts.country_id + district_zone.
     */
    public function zones(Country $country): JsonResponse
    {
        $zones = Zone::query()
            ->select(['zones.id', 'zones.name', 'zones.code'])
            ->join('district_zone', 'district_zone.zone_id', '=', 'zones.id')
            ->join('districts', 'districts.id', '=', 'district_zone.district_id')
            ->where('districts.country_id', $country->id)
            ->where('zones.is_active', true)
            ->where('districts.is_active', true)
            ->distinct()
            ->orderBy('zones.name')
            ->get();

        return response()->json($zones);
    }
    
    /**
     * Return only districts/cities belonging to the selected country and zone.
     */
    public function districts(Country $country, Zone $zone): JsonResponse
    {
        $districts = District::query()
            ->select(['districts.id', 'districts.name', 'districts.code'])
            ->join('district_zone', 'district_zone.district_id', '=', 'districts.id')
            ->where('district_zone.zone_id', $zone->id)
            ->where('districts.country_id', $country->id)
            ->where('districts.is_active', true)
            ->orderBy('districts.name')
            ->get();

        return response()->json($districts);
        return view('web.public.individual.create', compact(
            'countries',
            'sports',
            'committees',
            'defaultCountryId',
            'entities'
        ));
    }

    public function store(
        CreatePublicIndividualRequest $request,
        CreateIndividualAction $createIndividual): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Create User
            $createUser = new CreateUserAction;
            $createUserResult = $createUser([
                'name' => $request->email,
                'email' => $request->email,
                'password' => $request->password,
                'password_confirmation' => $request->password_confirmation,
                'role' => 'INDIVIDUAL',
                'active' => true,
            ]);

            $user = $createUserResult['user'];

            // Get the default federation for automatic assignment
            $defaultFederation = \Domain\Federations\Models\Federation::where('is_default_federation', true)->first();

            $data = $request->validated();
            $data['country_id'] = $request['individual_country_id'];
            $data['district_id'] = $request['district_id'];

            // A non-listed territory has no district reference.
            if (TerritorySelection::isNotListed($data['district_id'] ?? null)) {
                $data['district_id'] = null;
            }

            // Auto-assign the default federation
            if ($defaultFederation) {
                $data['federation_id'] = [$defaultFederation->id];
            }

            $individual = $createIndividual(IndividualData::fromArray($data, $user->id));

            // Assign member number to the new individual
            $memberNumberService = new MemberNumberService;
            $memberNumberService->assignIndividualMemberNumber($individual);

            // Handle photo upload - simplified best practice approach
            if ($request->hasFile('logo')) {
                $uploadedFile = $request->file('logo');

                // Ensure the file is valid before processing
                if ($uploadedFile && $uploadedFile->isValid()) {
                    try {
                        // Direct upload to media library - no temp files needed
                        $individual->addMedia($uploadedFile)
                            ->toMediaCollection('profile');
                    } catch (Exception $e) {
                        // Log the error but don't fail the entire registration
                        Log::warning('Failed to upload profile photo for individual ID ' . $individual->id . ': ' . $e->getMessage());
                    }
                } else {
                    // Log invalid file upload attempt
                    Log::warning('Invalid file upload attempt for individual ID ' . $individual->id);
                }
            }

            // Create entity affiliation if selected
            if (! empty($request->entity_id)) {
                $entity = Entity::findOrFail($request->entity_id);
                $createIndividualEntity = new CreateIndividualEntityAction;
                $createIndividualEntity->execute($individual->member_code, $request->entity_id);
            }

            DB::commit();

            // Try to send email verification, but don't fail the registration if email fails
            try {
                $user->sendEmailVerificationNotification();
                $emailSent = true;
            } catch (Exception $emailException) {
                // Log the email error but continue - registration was successful
                Log::warning('Failed to send verification email to ' . $user->email . ': ' . $emailException->getMessage());
                $emailSent = false;
            }
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex->getCode().': '.$ex->getMessage());

            return redirect()->route('public.individual.create')->with('error', 'Error creating this record, please contact the administrator.');
        }

        // Adjust success message based on whether email was sent
        if (! $emailSent) {
            return redirect()->route('public.individual.create')->with('success', 'Individual created successfully. Please contact support if you did not receive the verification email.');
        }

        return redirect()->route('public.individual.create')->with('success', 'Individual created with success. Please check your email to complete the verification process.');
    }

    /**
     * Resolve the city selected by the user and reject forged country/zone/district
     * combinations. "outside_portugal" is kept as the legacy sentinel used by the
     * existing request/form and now means "Vide endereço".
     */
    private function validatedDistrictId(CreatePublicIndividualRequest $request): ?int
    {
        $districtId = $request->input('district_id');
        $zoneId = $request->input('zone_id');
        $countryId = $request->input('individual_country_id');

        if ($districtId === 'outside_portugal' || $districtId === null || $districtId === '') {
            if (blank($request->input('address'))) {
                throw ValidationException::withMessages([
                    'address' => 'Como a UF/cidade não está cadastrada, informe a UF/estado/província e a cidade no campo Endereço.',
                ]);
            }

            return null;
        }

        if (! ctype_digit((string) $districtId) || ! ctype_digit((string) $zoneId) || ! ctype_digit((string) $countryId)) {
            throw ValidationException::withMessages([
                'district_id' => 'Selecione uma cidade válida ou use a opção “Vide endereço”.',
            ]);
        }

        $isValid = District::query()
            ->join('district_zone', 'district_zone.district_id', '=', 'districts.id')
            ->where('districts.id', (int) $districtId)
            ->where('districts.country_id', (int) $countryId)
            ->where('district_zone.zone_id', (int) $zoneId)
            ->exists();

        if (! $isValid) {
            throw ValidationException::withMessages([
                'district_id' => 'A cidade selecionada não pertence ao país/UF informados.',
            ]);
        }

        return (int) $districtId;
    }

}
