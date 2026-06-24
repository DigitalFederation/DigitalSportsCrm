<?php

namespace App\Http\Controllers\Federation;

use App\Http\Controllers\Controller;
use App\Http\Requests\FederationIndividualEditRequest;
use App\Http\Requests\IndividualCreateRequest;
use App\Http\Requests\UpdateIndividualEmailRequest;
use App\Models\Country;
use Domain\Documents\Models\Document;
use Domain\Entities\Models\Entity;
use Domain\Federations\Actions\UpdateIndividualEmailAction;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Actions\CreateIndividualAction;
use Domain\Individuals\Actions\EditIndividualAction;
use Domain\Individuals\DataTransferObject\IndividualData;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\States\ActiveIndividualFederationState;
use Domain\Memberships\Services\MemberNumberService;
use Domain\Memberships\States\ActiveAffiliationState;
use Domain\OfficialDocuments\Models\OfficialDocument;
use Domain\Users\Actions\CreateUserAction;
use Domain\Users\Actions\SyncUserRolesAction;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndividualController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        // Ensure user is authenticated and is a federation user
        $user = Auth::user();
        if (! $user || ! $user->isFederation()) {
            abort(403, 'Unauthorized action.');
        }
        $loggedInFederation = $user->federations()->first();
        if (! $loggedInFederation) {
            Log::error('Federation user has no associated federation.', ['user_id' => $user->id]);
            abort(403, 'Federation association not found.');
        }
        $loggedInFederationId = $loggedInFederation->id;

        $individuals = QueryBuilder::for(Individual::class)
            ->allowedFilters([
                AllowedFilter::scope('filter_name'),
                AllowedFilter::scope('filter_surname'),
                AllowedFilter::scope('filter_country'),
                AllowedFilter::scope('filter_member_code'),
                AllowedFilter::scope('filter_instructors'),
                AllowedFilter::scope('filter_entity'),
                AllowedFilter::scope('committee'),
                AllowedFilter::scope('instructors'),
                AllowedFilter::scope('coachs'),
                AllowedFilter::scope('referees'),
                AllowedFilter::exact('member_number'),
                AllowedFilter::scope('filter_national_affiliation_status', 'filterNationalAffiliationStatus'),
                AllowedFilter::exact('gender'),
            ])
            ->withCount(['affiliations as has_active_validation_plan' => function ($q) {
                $q->where('end_date', '>=', now())
                    ->where('status_class', ActiveAffiliationState::class)
                    ->whereHas('federation', fn ($f) => $f->where('is_default_federation', true))
                    ->whereHas('memberSubscription.membershipPackage.affiliationPlans', function ($ap) {
                        $ap->where('is_validation_plan', true);
                    });
            }])
            ->whereHas(
                'individualFederations',
                function ($query) use ($loggedInFederationId) {
                    return $query->where('federation_id', $loggedInFederationId)
                        ->where('status_class', ActiveIndividualFederationState::class);
                }
            )
            ->with([
                'country',
                'individualFederations' => function ($query) use ($loggedInFederationId) {
                    $query->where('federation_id', $loggedInFederationId);
                },
            ])
            ->defaultSort('-created_at')
            ->allowedSorts('name', 'birthdate', 'created_at')
            ->paginate()
            ->appends(request()->query());

        $countries = Country::select('id', 'name')->orderBy('name')->get();
        $entities = Entity::fromFederation($loggedInFederationId)->select('id', 'name')->orderBy('name')->get();

        $affiliationStatuses = [
            'active' => __('individuals.active'),
            'inactive' => __('individuals.inactive'),
        ];

        $genders = [
            'male' => __('individuals.male'),
            'female' => __('individuals.female'),
        ];

        return view('web.federation.individual.index', compact('individuals', 'countries', 'entities', 'affiliationStatuses', 'genders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $user = Auth::user();
        if (! $user || ! $user->isFederation()) {
            abort(403, 'Unauthorized action.');
        }
        $federation = $user->federations()->with('entities')->first();
        if (! $federation) {
            Log::error('Federation user has no associated federation for create.', ['user_id' => $user->id]);
            abort(403, 'Federation association not found.');
        }

        $countries = Country::select('id', 'name')->orderBy('name')->get();
        $districts = \Domain\Geographic\Models\District::where('is_active', true)->orderBy('name')->get();
        $individual = new Individual;

        return view('web.federation.individual.create', compact('countries', 'federation', 'individual', 'districts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(
        IndividualCreateRequest $request,
        CreateIndividualAction $createIndividual
    ): RedirectResponse {

        try {
            DB::beginTransaction();

            // Use validated data
            $validatedData = $request->validated();

            // Convert "outside_portugal" to null for district_id
            if (isset($validatedData['district_id']) && $validatedData['district_id'] === 'outside_portugal') {
                $validatedData['district_id'] = null;
            }

            // Determine user role based on member categories
            $role = $this->determineUserRole($validatedData['member_categories'] ?? []);

            $create_user = new CreateUserAction;
            $createUserResult = $create_user([
                'email' => $validatedData['email'],
                'name' => $validatedData['name'],
                'role' => $role,
                'bypass_verification' => true, // Federation creates pre-verified accounts
            ]);
            $user = $createUserResult['user'];

            // Prepare data for individual creation
            $individualData = $validatedData;
            $individualData['district_id'] = $validatedData['district_id'];
            unset($individualData['logo']);

            $individual = $createIndividual(
                IndividualData::fromArray($individualData, $user->id),
                true
            );

            // Assign member number to the new individual
            $memberNumberService = new MemberNumberService;
            $memberNumberService->assignIndividualMemberNumber($individual);

            // Handle photo upload
            if ($request->hasFile('logo')) {
                $individual->addMedia($request->file('logo'))
                    ->toMediaCollection('profile');
            }

            // Create entity affiliation if selected
            if (! empty($validatedData['entity_id'])) {
                $createIndividualEntity = new \Domain\Individuals\Actions\CreateIndividualEntityAction;
                $createIndividualEntity->execute($individual->member_code, (int) $validatedData['entity_id']);
            }

            DB::commit();

            return redirect(route('federation.individual.index'))->with('success', 'Individual created with success.');
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex->getCode() . ': ' . $ex->getMessage());

            return back()->with('error', 'Error creating this record, please contact the administrator.');
        }
    }

    /**
     * Determine the user role based on member categories
     */
    private function determineUserRole(array $memberCategories): string
    {
        // For now, always return INDIVIDUAL as the other groups may not exist
        // The actual role assignment can be handled later through the role sync process
        return 'INDIVIDUAL';

        /* Future implementation when groups are properly set up:
        if (empty($memberCategories)) {
            return 'INDIVIDUAL';
        }

        // Check if diving professional is selected
        if (in_array('diving_professional', $memberCategories)) {
            return 'INDIVIDUAL-DIVING';
        }

        // Check if sport practitioner or coach/referee is selected
        if (in_array('sport_practitioner', $memberCategories) || in_array('coach_referee', $memberCategories)) {
            return 'INDIVIDUAL-SPORT';
        }

        // Default to INDIVIDUAL for recreational divers or no categories
        return 'INDIVIDUAL';
        */
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View|RedirectResponse
    {
        $user = Auth::user();
        if (! $user || ! $user->isFederation()) {
            abort(403, 'Unauthorized action.');
        }
        $loggedInFederation = $user->federations()->first();
        if (! $loggedInFederation) {
            Log::error('Federation user has no associated federation.', ['user_id' => $user->id]);

            return redirect()->route('dashboard')->with('error', 'Unable to determine your federation.'); // Adjust route as needed
        }
        $federationId = $loggedInFederation->id;
        $isMainFederation = $loggedInFederation->isMainFederation();

        $individual = Individual::with([
            'user', // Load user if needed (e.g., for potential future use in view)
            'certificationsDivingAttributed',
            'certificationsScientificAttributed', // Added based on unified view
            'certificationsSportAttributed',
            'licenses', // Unified licenses relationship
            'individualEntities.entity' => function ($query) use ($federationId) {
                $query->whereHas('federations', function ($q) use ($federationId) {
                    $q->where('federation_id', $federationId);
                });
            },
            'individualFederations' => function ($query) use ($federationId) {
                $query->with('federation.country')
                    ->where('federation_id', $federationId);
            },
            'federationProfessionalRoles' => function ($query) {
                $query->whereHas('professionalRole', function ($q) {
                    $q->where('role', 'STAFF'); // Keep this specific logic if needed
                });
            },
            // Load active member subscriptions with insurances and affiliations
            'memberSubscriptions' => function ($query) use ($federationId, $isMainFederation) {
                $query->whereIn('status_class', [
                    'Domain\Memberships\States\ActiveMemberSubscriptionState',
                    'Domain\Memberships\States\PendingPaymentMemberSubscriptionState',
                ])->with([
                    'membershipPackage',
                    'affiliations.federation',
                    'affiliations.memberSubscription.membershipPackage.affiliationPlans',
                ]);

                // Apply insurance filtering based on federation access
                if ($isMainFederation) {
                    // Main federation sees all insurances
                    $query->with('insurances.insurancePlan');
                } else {
                    // Local federation only sees insurances if individual has active affiliation
                    $query->with(['insurances' => function ($insQuery) use ($federationId) {
                        $insQuery->whereHas('memberSubscription', function ($subQuery) use ($federationId) {
                            $subQuery->whereHas('affiliations', function ($affQuery) use ($federationId) {
                                $affQuery->where('federation_id', $federationId)
                                    ->where('status_class', 'Domain\Memberships\States\ActiveAffiliationState');
                            });
                        })->with('insurancePlan');
                    }]);
                }
            },
        ])
            ->where(compact('id'))
            ->first();

        if (empty($individual)) {
            return redirect(route('federation.individual.index'))->with('error', 'Error finding the individual.');
        }

        // Fetch official documents consistently
        $official_documents_query = OfficialDocument::with('media')
            ->has('media')
            ->where('individual_id', $individual->id);

        if ($loggedInFederation->is_local) {
            $official_documents_query->where('federation_id', $loggedInFederation->id);
        } else {
            // Assuming non-local federations might see country-level or non-federation specific docs
            // Adjust this logic based on requirements. Maybe filter by country_id or allow null federation_id?
            $official_documents_query->where(function ($query) use ($loggedInFederation) {
                $query->where('country_id', $loggedInFederation->country_id)
                    ->orWhereNull('federation_id'); // Example: Include general docs too
            });
        }
        $official_documents = $official_documents_query->get();

        // Load payment documents for the individual
        $payment_documents = Document::whereIn('owner_type', Document::ownerTypeValuesFor(Individual::class))
            ->where('owner_id', $individual->id)
            ->with(['transactions', 'type', 'method'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Pass the context variable
        return view('web.common.individual.show', [
            'individual' => $individual,
            'official_documents' => $official_documents,
            'payment_documents' => $payment_documents,
            'context' => 'federation',
        ]);
    }

    public function files(string $id): View
    {
        $user = Auth::user();
        if (! $user || ! $user->isFederation()) {
            abort(403, 'Unauthorized action.');
        }
        $federation = $user->federations()->first();
        if (! $federation) {
            Log::error('Federation user has no associated federation for files.', ['user_id' => $user->id]);
            abort(403, 'Federation association not found.');
        }

        $individual = Individual::findOrFail($id);
        $official_documents = OfficialDocument::with('media', 'country')
            ->where('individual_id', $individual->id)
            ->where(function (Builder $query) use ($federation) {
                $query->where('federation_id', $federation->id)
                    ->orWhereNull('federation_id');
            })
            ->get();

        $files = $official_documents->map(function ($official_document) {
            return $official_document->getMedia('media');
        });

        $official_document_types = \App\Enums\OfficialDocumentTypeEnum::individualTypes();

        $countries = Country::select('id', 'name')
            ->whereHas('federations', function ($query) use ($federation) {
                return $query->where('id', $federation->id);
            })
            ->orderBy('name')->get();

        return view('web.common.official_documents.files', compact('individual', 'official_documents', 'official_document_types', 'files', 'countries'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View|RedirectResponse
    {
        $user = Auth::user();
        if (! $user || ! $user->isFederation()) {
            abort(403, 'Unauthorized action.');
        }
        $federation = $user->federations()->with('entities')->get();
        if ($federation->isEmpty()) {
            Log::error('Federation user has no associated federation for edit.', ['user_id' => $user->id]);
            abort(403, 'Federation association not found.');
        }

        $individual = Individual::findOrFail($id);
        $countries = Country::select('id', 'name')->orderBy('name')->get();

        // Pass the federation collection
        return view('web.federation.individual.edit', compact('individual', 'countries', 'federation'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        FederationIndividualEditRequest $request,
        string $individualId,
        EditIndividualAction $editIndividual
    ): RedirectResponse {

        $user = Auth::user();
        if (! $user || ! $user->isFederation()) {
            abort(403, 'Unauthorized action.');
        }
        $federation = $user->federations()->first();
        if (! $federation) {
            Log::error('Federation user has no associated federation for update.', ['user_id' => $user->id]);
            abort(403, 'Federation association not found.');
        }

        $individual = Individual::findOrFail($individualId);

        // Use validated data
        $validatedData = $request->validated();

        // Data to pass to the action
        $actionData = $validatedData;

        // Check if the user is not part of the international group and remove fields if necessary
        /*
        if ($user->group->code !== 'ADMIN') {
            // Remove fields directly from the array passed to the action
            unset(
                $actionData['birthdate'],
                $actionData['country_id'],
                $actionData['name'],
                $actionData['surname'],
                $actionData['native_name'],
                $actionData['gender'],
                $actionData['national_federation_number'] // Also unset from action data if not allowed
            );
        }
        */

        try {
            DB::beginTransaction();

            $updated = $editIndividual(IndividualData::fromArray($actionData, $individual->user_id), $individualId);

            DB::commit();

            // Log activity using the *original* validated data for full context
            activity('Individual Update')
                ->performedOn($individual)
                ->causedBy($user)
                ->event('updated')
                ->withProperties([
                    'individual_id' => $individual->id,
                    'user_id' => $individual->user_id,
                    'federation_id' => $federation->id,
                    'changes' => $validatedData, // Log all validated changes
                ])
                ->log('Individual updated: ' . $individual->name . ' by ' . $user->name);

            if ($updated) {
                return back()->with('success', 'Individual updated with success.');
            } else {
                Log::error('Individual wasn\'t updated but there is no errors.');

                return back()->with('error', 'Error updating a individual.');
            }
        } catch (Exception $ex) {
            Log::error($ex->getMessage());

            return back()->with('error', 'Error updating the individual:' . ' ' . $ex->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $user = Auth::user();
        if (! $user || ! $user->isFederation()) {
            abort(403, 'Unauthorized action.');
        }
        $federation = $user->federations()->first();
        if (! $federation) {
            Log::error('Federation user has no associated federation for destroy.', ['user_id' => $user->id]);
            abort(403, 'Federation association not found.');
        }

        $syncUserRolesAction = new SyncUserRolesAction;
        $individual = Individual::with('user')->findOrFail($id);
        $individualUser = $individual->user;
        try {
            $detached = $federation->individuals()->detach($id);

            if ($detached) {
                return redirect()->back()->with('success', 'Individual detached with success.');
            } else {
                Log::error('Individual don\'t was detached but there is no errors.');

                return redirect()->back()->with('error', "The individual hasn't been detached.");
            }
        } catch (Exception $ex) {
            return redirect()->back()->with('error', $ex->getMessage());
        } finally {
            $syncUserRolesAction->execute($individualUser);
        }
    }

    public function showUpdateEmailForm(Individual $individual)
    {
        $this->checkAuthorization($individual);

        return view('web.federation.individual.update-email', compact('individual'));
    }

    public function updateEmail(
        UpdateIndividualEmailRequest $request,
        Individual $individual,
        UpdateIndividualEmailAction $action
    ): RedirectResponse {
        $this->checkAuthorization($individual);

        // Use validated data
        $validated = $request->validated();
        $publicEmail = $validated['public_email'];
        // Check the validated boolean field, defaulting to false if not present/validated
        $updateLoginEmail = $validated['update_login_email'] ?? false;
        $loginEmail = $updateLoginEmail ? ($validated['login_email'] ?? null) : null;

        if ($action->execute($individual, $publicEmail, $loginEmail)) {
            return redirect()->route('federation.individual.show', $individual)
                ->with('success', 'Email updated successfully.');
        }

        return back()->with('error', 'Unable to update email. Please try again or contact support.');
    }

    private function checkAuthorization(Individual $individual)
    {
        $user = Auth::user();
        if (! $user->isFederation()) {
            abort(403, 'Unauthorized action.');
        }

        $userFederationId = $user->getFederationId();
        if (! $userFederationId) {
            abort(403, 'User is not associated with any federation.');
        }

        $individualBelongsToFederation = $individual->individualFederations()
            ->where('federation_id', $userFederationId)
            ->where('status_class', ActiveIndividualFederationState::class)
            ->exists();

        if (! $individualBelongsToFederation) {
            abort(403, 'Unauthorized action. This individual does not belong to your federation.');
        }
    }
}
