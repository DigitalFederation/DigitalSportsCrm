<?php

namespace App\Http\Controllers\Entity;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndividualCreateRequest;
use App\Http\Requests\IndividualEditRequest;
use App\Models\Country;
use App\Models\GeoZone;
use App\Models\SubRegion;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Actions\CreateIndividualAction;
use Domain\Individuals\Actions\DetectIfIndividualIsInstructorAction;
use Domain\Individuals\Actions\EditIndividualAction;
use Domain\Individuals\DataTransferObject\IndividualData;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\States\ActiveIndividualEntityState;
use Domain\Memberships\Services\MemberNumberService;
use Domain\OfficialDocuments\Models\OfficialDocument;
use Domain\Users\Actions\CreateUserAction;
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
        $user = Auth::user();
        if (! $user || ! $user->isEntity()) {
            abort(403, 'Unauthorized action.');
        }
        $entityId = $user->getEntityId();
        if (! $entityId) {
            Log::error('Entity user has no associated entity for index.', ['user_id' => $user->id]);
            abort(403, 'Entity association not found.');
        }

        $individuals = QueryBuilder::for(Individual::class)
            ->allowedFilters([
                AllowedFilter::scope('filter_name'),
                AllowedFilter::scope('filter_surname'),
                AllowedFilter::scope('filter_country'),
                AllowedFilter::scope('filter_member_code'),
                AllowedFilter::scope('filter_member_number'),
                AllowedFilter::scope('filter_federation'),
                AllowedFilter::scope('filter_instructors'),
                AllowedFilter::scope('filter_entity'),
                AllowedFilter::scope('filter_zone'),
                AllowedFilter::scope('filter_region'),
                AllowedFilter::scope('filter_national_affiliation_status'),
            ])
            ->whereHas('individualEntities', function ($q) use ($entityId) {
                return $q->where('entity_id', $entityId)
                    ->where('status_class', ActiveIndividualEntityState::class);
            })
            ->with([
                'country.geoZone',
                'country.subRegion',
                'affiliations' => function ($query) {
                    $query->whereIn('status_class', [
                        'Domain\Affiliations\States\ActiveAffiliationState',
                        'Domain\Memberships\States\ActiveAffiliationState',
                        'Domain\Affiliations\States\ExpiredAffiliationState',
                        'Domain\Memberships\States\ExpiredAffiliationState',
                    ]);
                },
            ])
            ->latest()
            ->paginate()
            ->appends(request()->query());

        $countries = Country::select('id', 'name')->orderBy('name')->get();
        $entity = $user->entities()->first();
        if (! $entity) {
            Log::error('Entity user has no associated entity for index filters.', ['user_id' => $user->id]);
            $federations = collect();
        } else {
            $federations = Federation::select('id', 'name')->whereHas('entities', function (Builder $query) use ($entityId) {
                $query->where('entity_id', $entityId);
            })->orderBy('name')->get();
        }
        $entities = Entity::select('id', 'name')->orderBy('name')->get();
        $zones = GeoZone::select('id', 'name')->orderBy('name')->get();
        $regions = SubRegion::select('id', 'name')->orderBy('name')->get();

        return view('web.entity.individual.index', compact('individuals', 'countries', 'federations', 'entities', 'zones', 'regions'));
    }

    public function create(): View
    {
        $user = Auth::user();
        if (! $user || ! $user->isEntity()) {
            abort(403, 'Unauthorized action.');
        }
        $entity = $user->entities()->first();
        if (! $entity) {
            Log::error('Entity user has no associated entity for create.', ['user_id' => $user->id]);
            abort(403, 'Entity association not found.');
        }

        $federation = Federation::select('id', 'name')
            ->whereHas('entities', function (Builder $query) use ($entity) {
                $query->where('entity_id', $entity->id);
            })
            ->whereNull('parent_id')
            ->first();

        $countries = Country::select('id', 'name')->orderBy('name')->get();
        $districts = \Domain\Geographic\Models\District::where('is_active', true)->orderBy('name')->get();

        $individual = new Individual;

        return view('web.entity.individual.create', compact('federation', 'individual', 'countries', 'entity', 'districts'));
    }

    public function store(
        IndividualCreateRequest $request,
        CreateIndividualAction $createIndividual
    ): RedirectResponse {
        // Increase time limit for this operation due to QR generation and email sending
        set_time_limit(120);

        $user = Auth::user();
        if (! $user || ! $user->isEntity()) {
            abort(403, 'Unauthorized action.');
        }
        $entity = $user->entities()->first();
        if (! $entity) {
            Log::error('Entity user has no associated entity for store.', ['user_id' => $user->id]);
            abort(403, 'Entity association not found.');
        }

        try {
            DB::beginTransaction();

            $validatedData = $request->validated();

            // Convert "outside_portugal" to null for district_id
            if (isset($validatedData['district_id']) && $validatedData['district_id'] === 'outside_portugal') {
                $validatedData['district_id'] = null;
            }

            // Get all federations associated with this entity (including local federations)
            $entityFederations = $entity->federations()
                ->where('entity_federation.active', 1)
                ->pluck('federation.id')
                ->toArray();

            // If entity has federations, use them; otherwise keep the original federation_id
            if (! empty($entityFederations)) {
                $validatedData['federation_id'] = $entityFederations;
            }

            $create_user = new CreateUserAction;
            $createUserResult = $create_user([
                'email' => $validatedData['email'],
                'name' => $validatedData['name'],
                'role' => 'INDIVIDUAL',
                'bypass_verification' => true,
            ]);
            $createdUser = $createUserResult['user'];

            $individual = $createIndividual(
                IndividualData::fromArray($validatedData, $createdUser->id),
                false,  // $addedByFederation
                true    // $addedByEntity
            );

            // Assign member number to the new individual
            $memberNumberService = new MemberNumberService;
            $memberNumberService->assignIndividualMemberNumber($individual);

            $individualEntity = $individual->individualEntities()->where('entity_id', $entity->id)->firstOrFail();
            $individualEntity->update(['status_class' => ActiveIndividualEntityState::class]);

            activity('Individual')
                ->performedOn($individual)
                ->causedBy($user)
                ->event('created')
                ->withProperties([
                    'individual_id' => $individual->id,
                    'user_id' => $createdUser->id,
                    'entity_id' => $entity->id,
                    'data' => $validatedData,
                ])
                ->log('Individual created: ' . $individual->name . ' by ' . $user->name);
            DB::commit();

            return redirect(route('entity.individual.index'))->with('success', 'Individual created with success.');
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex->getCode() . ': ' . $ex->getMessage());

            return redirect()->back()->with('error', 'Error creating this record, please contact the administrator.');
        }
    }

    public function files(string $id): View
    {
        $user = Auth::user();
        if (! $user || ! $user->isEntity()) {
            abort(403, 'Unauthorized action.');
        }
        $entity = $user->entities()->first();
        if (! $entity) {
            Log::error('Entity user has no associated entity for files.', ['user_id' => $user->id]);
            abort(403, 'Entity association not found.');
        }
        $federation = $entity->federations()->first();
        if (! $federation) {
            Log::warning('Entity has no associated federation for files.', ['entity_id' => $entity->id]);
        }

        $individual = Individual::findOrFail($id);

        $official_documents = OfficialDocument::with('media', 'country')
            ->where('individual_id', $individual->id)
            ->where(function (Builder $query) use ($federation) {
                if ($federation) {
                    $query->where('federation_id', $federation->id)
                        ->orWhereNull('federation_id');
                } else {
                    $query->whereNull('federation_id');
                }
            })
            ->get();

        $files = $official_documents->map(function ($official_document) {
            return $official_document->getMedia('media');
        });

        $official_document_types = \App\Enums\OfficialDocumentTypeEnum::individualTypes();

        $countries = collect();
        if ($federation) {
            $countries = Country::select('id', 'name')
                ->whereHas('federations', function ($query) use ($federation) {
                    return $query->where('id', $federation->id);
                })
                ->orderBy('name')->get();
        }

        return view('web.common.official_documents.files', compact('individual', 'official_documents', 'official_document_types', 'files', 'countries'));
    }

    public function show(
        string $id,
        DetectIfIndividualIsInstructorAction $detectIsInstructor
    ): View|RedirectResponse {

        $user = Auth::user();
        if (! $user || ! $user->isEntity()) {
            abort(403, 'Unauthorized action.');
        }
        $entityId = $user->getEntityId();
        if (! $entityId) {
            Log::error('Entity user has no associated entity for show.', ['user_id' => $user->id]);

            return redirect()->route('dashboard')->with('error', 'Unable to determine your entity.');
        }

        $individual = Individual::where('id', $id)
            ->individualsFromEntity()
            ->with([
                'user',
                'certificationsDivingAttributed',
                'certificationsScientificAttributed',
                'certificationsSportAttributed',
                'licenses',
                'individualFederations',
                'individualFederations.federation',
                'individualFederations.federation.country',
                'individualEntities' => function ($query) use ($entityId) {
                    $query->where('entity_id', $entityId);
                },
                'individualEntities.entity',
                // Load active member subscriptions with insurances and affiliations
                'memberSubscriptions' => function ($query) {
                    $query->whereIn('status_class', [
                        'Domain\Memberships\States\ActiveMemberSubscriptionState',
                        'Domain\Memberships\States\PendingPaymentMemberSubscriptionState',
                    ])->with([
                        'membershipPackage.affiliationPlans',
                        'insurances.insurancePlan',
                        'affiliations.federation',
                        'affiliations.memberSubscription.membershipPackage.affiliationPlans',
                    ]);
                },
            ])
            ->first();

        if (empty($individual)) {
            $exists = Individual::where(compact('id'))->exists();
            if (! $exists) {
                return redirect(route('entity.individual.index'))->with('error', 'Error finding the individual.');
            }

            return redirect(route('entity.individual.index'))->with('error', 'Individual not found within this entity.');
        }

        // Load official documents for the individual
        $official_documents = OfficialDocument::with('media')
            ->has('media')
            ->where('individual_id', $individual->id)
            ->get();

        return view('web.common.individual.show', [
            'individual' => $individual,
            'official_documents' => $official_documents,
            'context' => 'entity',
        ]);
    }

    public function edit(string $id): View|RedirectResponse
    {
        /*
        $individual = Individual::findOrFail($id);
        $countries = Country::select('id', 'name')->orderBy('name')->get();

        $federations = Federation::select('id', 'name')->whereHas('entities', function (Builder $query) {
            $query->where('entity_id', auth()->user()->entities()->first()->id);
        })->whereNull('parent_id')->orderBy('name')->get();

        return view('web.entity.individual.edit', compact('individual', 'countries', 'federations'));
        */
        abort(501, 'Not Implemented');
    }

    public function update(IndividualEditRequest $request, string $individualId, EditIndividualAction $editIndividual): RedirectResponse
    {
        /*
        //TODO: Remover e substituir o $userID no INdividualData
        $individual = Individual::findOrFail($individualId);
        try {
            DB::beginTransaction();

            $updated = $editIndividual(IndividualData::fromArray($request->validated(), $individual->user_id), $individualId);

            DB::commit();
            if ($updated) {
                return redirect(route('entity.individual.index'))->with('success', 'Individual updated with success.');
            } else {
                Log::error('Individual wasn\'t updated but there is no errors.');

                return back()->with('error', 'Error updating a individual.');
            }
        } catch (Exception $ex) {
            Log::error($ex->getCode().': '.$ex->getMessage());

            return back()->with('error', 'Error updating the individual, please contact the please contact the administration.');
        }
        */
        abort(501, 'Not Implemented');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $user = Auth::user();
        if (! $user || ! $user->isEntity()) {
            abort(403, 'Unauthorized action.');
        }
        $entity = $user->entities()->first();
        if (! $entity) {
            Log::error('Entity user has no associated entity for destroy.', ['user_id' => $user->id]);
            abort(403, 'Entity association not found.');
        }

        try {
            $detached = $entity->individuals()->detach($id);

            if ($detached) {
                return redirect()->back()->with('success', 'Individual detached with success.');
            } else {
                Log::error('Individual don\'t was detached but there is no errors.');

                return redirect()->back()->with('error', "The individual hasn't been detached.");
            }
        } catch (Exception $ex) {
            return redirect()->back()->with('error', $ex->getMessage());
        }
    }
}
