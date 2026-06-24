<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePublicIndividualRequest;
use App\Models\Committee;
use App\Models\Country;
use App\Models\Sport;
use Domain\Entities\Models\Entity;
use Domain\Geographic\Models\District;
use Domain\Individuals\Actions\CreateIndividualAction;
use Domain\Individuals\Actions\CreateIndividualEntityAction;
use Domain\Individuals\DataTransferObject\IndividualData;
use Domain\Memberships\Services\MemberNumberService;
use Domain\Users\Actions\CreateUserAction;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IndividualController extends Controller
{
    public function create(): View
    {
        // List of sports
        $sports = Sport::query()->orderBy('name')->pluck('name', 'id');
        // List of Committees
        $committees = Committee::query()->orderBy('name')->pluck('name', 'id');
        // Countries - still needed for individual nationality
        $countries = Country::query()->orderBy('name')->pluck('name', 'id');
        // Districts
        $districts = District::query()->orderBy('name')->pluck('name', 'id');
        // List of active entities (entities with active federation status)
        $entities = Entity::whereHas('federations', function ($query) {
            $query->where('entity_federation.status_class', \Domain\Entities\States\ActiveEntityFederationState::class);
        })
            ->orderBy('name')
            ->pluck('name', 'id');

        return view('web.public.individual.create', compact('countries', 'sports', 'committees', 'districts', 'entities'));
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

            // Convert "outside_portugal" to null for district_id
            if (isset($data['district_id']) && $data['district_id'] === 'outside_portugal') {
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
}
