<?php

namespace App\Http\Controllers\Federation;

use App\Http\Controllers\Controller;
use App\Http\Requests\EntityEditRequest;
use App\Http\Requests\FederationEntityCreateRequest;
use App\Models\Committee;
use App\Models\Country;
use App\Models\Group;
use Domain\Entities\Actions\AssociateUserToEntityAction;
use Domain\Entities\Actions\CreateEntityAction;
use Domain\Entities\Actions\EditEntityAction;
use Domain\Entities\DataTransferObject\EntityData;
use Domain\Entities\Models\Entity;
use Domain\Entities\Models\EntityFederation;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Geographic\Models\District;
use Domain\Geographic\Models\Zone;
use Domain\Memberships\States\ActiveAffiliationState;
use Domain\Users\Actions\CreateUserAction;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class EntityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $entities = QueryBuilder::for(Entity::class)
            ->allowedFilters([
                AllowedFilter::scope('filter_name'),
                AllowedFilter::scope('filter_member_code'),
                AllowedFilter::exact('member_number'),
                AllowedFilter::scope('filter_district'),
                AllowedFilter::scope('filter_by_zone', 'filterByZone'),
                AllowedFilter::scope('affiliation_status', 'filterAffiliationStatus'),
                AllowedFilter::scope('committee'),
            ])
            ->withCount(['affiliations as has_active_validation_plan' => function ($q) {
                $q->where('end_date', '>=', now())
                    ->where('status_class', ActiveAffiliationState::class)
                    ->whereHas('federation', fn ($f) => $f->where('is_default_federation', true))
                    ->whereHas('memberSubscription.membershipPackage.affiliationPlans', function ($ap) {
                        $ap->where('is_validation_plan', true);
                    });
            }])
            ->whereHas('federations', function (Builder $query) {
                $query->select('federation.id')
                    ->where('federation.id', auth()->user()->federations()->value('federation.id'))
                    ->where('entity_federation.status_class', ActiveEntityFederationState::class);
            })
            ->allowedSorts('name')
            ->with('district', 'zones', 'entityFederations.federation')
            ->paginate()
            ->appends(request()->query());

        $districts = District::select('id', 'name')->orderBy('name')->get();
        $zones = Zone::active()->select('id', 'name')->orderBy('name')->get();

        $affiliationStatuses = [
            'active' => __('states.active'),
            'inactive' => __('states.inactive'),
            'pending' => __('states.pending'),
            'rejected' => __('states.rejected'),
        ];

        return view('web.federation.entity.index', compact('entities', 'districts', 'zones', 'affiliationStatuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $countries = Country::select('id', 'name')->orderBy('name')->get();
        $entity = new Entity;

        return view('web.federation.entity.create', compact('entity', 'countries'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(
        FederationEntityCreateRequest $request,
        CreateEntityAction $createEntityAction
    ): RedirectResponse {
        try {
            DB::beginTransaction();

            // Add federation_id to the validated data
            $validatedData = $request->validated();
            $validatedData['federation_id'] = auth()->user()->getFederationId();

            $entity = $createEntityAction(EntityData::fromArray($validatedData));

            // Create USER
            $randomPassword = Str::random(8);
            $createUser = new CreateUserAction;
            $createUserResult = $createUser([
                'name' => $request->user_email,
                'email' => $request->user_email,
                'password' => $randomPassword,
                'password_confirmation' => $randomPassword,
                'group_id' => Group::where('code', 'ENTITY')->value('id'),
                'active' => true, // Entity is automatically active when created by federation
            ]);

            $user = $createUserResult['user'];

            // Associate USER to Entity
            $associateUserToEntity = new AssociateUserToEntityAction;
            $associateUserToEntity($user, $entity, 'entity-admin');

            DB::commit();

            return redirect(route('federation.entity.index'))->with('success', 'Entity created with success.');
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex->getCode() . ': ' . $ex->getMessage());

            return back()->with('error', 'Error creating this record, please contact the administrator.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): View
    {
        $entity = Entity::with([
            'federations',
            'individuals',
            'memberSubscriptions.insurances.insurancePlan',
            'memberSubscriptions.affiliations.federation',
            'memberSubscriptions.affiliations.memberSubscription.membershipPackage.affiliationPlans',
        ])->whereHas('federations', function (Builder $query) {
            $query->where('federation.id', auth()->user()->federations()->value('federation.id'));
        })->findOrFail($id);

        // Load last 15 documents for the entity
        // Support both morph alias ('entity') and legacy full class name
        $documents = \Domain\Documents\Models\Document::with('type', 'transactions', 'details')
            ->where('owner_id', $entity->id)
            ->whereIn('owner_type', ['entity', Entity::class])
            ->whereNot('status_class', \Domain\Documents\States\DraftDocumentState::class)
            ->orderByDesc('created_at')
            ->take(15)
            ->get();

        $context = 'federation';

        return view('web.common.entity.show', compact('entity', 'documents', 'context'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): RedirectResponse|View
    {
        $federation = auth()->user()->federations()->first();

        // If user belongs to a local federation, redirect to show
        if ($federation->isLocal()) {
            return redirect()->route('federation.entity.show', $id);
        }

        $entity = Entity::where(compact('id'))
            ->whereHas('federations', function (Builder $query) use ($federation) {
                $query->where('federation.id', $federation->id);
            })
            ->with(['federations:id', 'committees:id', 'zones', 'entityFederations' => function ($query) use ($federation) {
                $query->where('federation_id', $federation->id);
            }])
            ->firstOrFail();

        // Attach the correct EntityFederation model to $entity->federation for form compatibility
        $entityFederation = $entity->entityFederations->first();
        $entity->setRelation('federation', $entityFederation);

        $countries = Country::select('id', 'name')->orderBy('name')->get();
        $committees = Committee::select('id', 'name')->orderBy('name')->get();
        // Get only local federations belonging to the main federation
        $localFederations = $federation->localFederations()->select('id', 'name')->orderBy('name')->get();

        return view('web.federation.entity.edit', compact('entity', 'countries', 'committees', 'localFederations'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        EntityEditRequest $entityEditRequest,
        int $id,
        EditEntityAction $editEntityAction
    ): RedirectResponse {
        try {
            $edit = $editEntityAction(EntityData::fromArray($entityEditRequest->validated()), $id);

            if (empty($edit)) {
                Log::error('Error finding the record: ' . json_encode($entityEditRequest->validated()));

                return back()->with('error', 'Error updating record.');
            }
        } catch (Exception $ex) {
            Log::error($ex->getCode() . ': ' . $ex->getMessage());

            return back()->with('error', 'Error updating the record, please contact the please contact the administration.');
        }

        return redirect(route('federation.entity.show', $id))->with('success', 'Record updated with success.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        try {
            $detached = auth()->user()->federations()->first()->entities()->detach($id);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());

            return back()->with('error', 'Error deleting the record, You can only delete entities without associated records.');
        }

        return redirect(route('federation.entity.index'))->with('success', 'Record deleted with success.');
    }
}
