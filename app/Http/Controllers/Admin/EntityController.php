<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\EntityEditRequest;
use App\Http\Requests\FederationEntityCreateRequest;
use App\Models\Committee;
use App\Models\Country;
use App\Models\Group;
use Domain\Documents\Models\Document;
use Domain\Documents\States\DraftDocumentState;
use Domain\Entities\Actions\AssociateUserToEntityAction;
use Domain\Entities\Actions\CreateEntityAction;
use Domain\Entities\Actions\DeleteEntityAction;
use Domain\Entities\Actions\EditEntityAction;
use Domain\Entities\DataTransferObject\EntityData;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Geographic\Models\District;
use Domain\Geographic\Models\Zone;
use Domain\Memberships\States\ActiveAffiliationState;
use Domain\Users\Actions\CreateUserAction;
use Domain\Users\Actions\SendWelcomeNotificationAction;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
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
                AllowedFilter::exact('has_international_portal'),
            ])
            ->withCount(['affiliations as has_active_validation_plan' => function ($q) {
                $q->where('end_date', '>=', now())
                    ->where('status_class', ActiveAffiliationState::class)
                    ->whereHas('federation', fn ($f) => $f->where('is_default_federation', true))
                    ->whereHas('memberSubscription.membershipPackage.affiliationPlans', function ($ap) {
                        $ap->where('is_validation_plan', true);
                    });
            }])
            ->with('district', 'zones', 'entityFederations.federation')
            ->orderBy('created_at', 'desc')
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

        $cmasPortalOptions = [
            '1' => __('individuals.yes'),
            '0' => __('individuals.no'),
        ];

        return view('web.admin.entity.index', compact('entities', 'districts', 'zones', 'affiliationStatuses', 'cmasPortalOptions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $federations = Federation::select('id', 'name')->orderBy('name')->get();
        $countries = Country::select('id', 'name')->orderBy('name')->get();
        $committees = Committee::select('id', 'name')->orderBy('name')->get();

        $entity = new Entity;

        return view('web.admin.entity.create', compact('entity', 'federations', 'countries', 'committees'));
    }

    public function store(
        FederationEntityCreateRequest $request,
        CreateEntityAction $createEntityAction
    ): RedirectResponse {

        try {
            DB::beginTransaction();

            $validated = $request->validated();

            $entity = $createEntityAction(EntityData::fromArray($validated));

            // Create User
            $randomPassword = Str::random(8);
            $createUser = new CreateUserAction;
            $createUserResult = $createUser([
                'name' => $validated['user_email'],
                'email' => $validated['user_email'],
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

            return redirect(route('admin.entity.index'))->with('success', 'Entity created with success.');
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex->getCode() . ': ' . $ex->getMessage());

            return back()->with('error', 'Error creating this record: ' . $ex->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View
    {
        $entity = Entity::with([
            'federations',
            'individuals',
            'users',
            'memberSubscriptions.insurances.insurancePlan',
            'memberSubscriptions.affiliations.federation',
            'memberSubscriptions.affiliations.memberSubscription.membershipPackage.affiliationPlans',
        ])->findOrFail($id);

        // Detect if user is a admin
        $user = Auth::user();
        $adminRoles = [
            'admin',
            'association-sport-admin',
            'association-scientific-admin',
            'association-admin',
            'admin-notifications',
        ];
        $isAdmin = $user && $user->hasAnyRole($adminRoles);

        // Detect if entity is related to the international federation
        $mainFederation = $entity->federations()->where('is_default_federation', true)->first();
        $isMainFederationEntity = $mainFederation !== null;

        $showCompanyView = $isAdmin && $isMainFederationEntity;

        // Always load last 15 documents for the entity
        // Support both morph alias ('entity') and legacy full class name
        $documents = Document::with('type', 'transactions', 'details')
            ->where('owner_id', $entity->id)
            ->whereIn('owner_type', ['entity', Entity::class])
            ->whereNot('status_class', DraftDocumentState::class)
            ->orderByDesc('created_at')
            ->take(15)
            ->get();

        $context = 'admin';

        return view('web.common.entity.show', compact(
            'entity',
            'showCompanyView',
            'documents',
            'context'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     *
     */
    public function edit(string $id): View|RedirectResponse
    {
        $entity = Entity::where(compact('id'))->with('federations:id', 'committees:id', 'zones', 'users')->firstOrFail();
        $federations = Federation::select('id', 'name')->orderBy('name')->get();
        $countries = Country::select('id', 'name')->orderBy('name')->get();
        $committees = Committee::select('id', 'name')->orderBy('name')->get();

        return view('web.admin.entity.edit', compact('entity', 'federations', 'countries', 'committees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        EntityEditRequest $request,
        string $id,
        EditEntityAction $editEntityAction
    ): RedirectResponse {

        $validated = $request->validated();
        try {
            DB::beginTransaction();
            $edit = $editEntityAction(EntityData::fromArray($validated), $id);
            // Fetch the Entity instance
            $entity = Entity::findOrFail($id);
            DB::commit();

            return redirect(route('admin.entity.edit', $id))->with('success', 'Record updated with success.');
        } catch (Exception $ex) {
            Log::error($ex->getCode() . ': ' . $ex->getMessage());

            return back()->with('error', 'Error updating this record: ' . $ex->getMessage());
        }
    }

    /**
     * Undocumented function
     */
    public function destroy(string $id, DeleteEntityAction $deleteEntityAction): RedirectResponse
    {
        try {
            db::beginTransaction();
            $deleteEntityAction($id);
            db::commit();
        } catch (Exception $ex) {
            db::rollBack();
            Log::error($ex->getCode() . ': ' . $ex->getMessage());

            return back()->with('error', 'Error deleting the record, You can only delete entities without associated records.');
        }

        return redirect(route('admin.entity.index'))->with('success', 'Record deleted with success.');
    }

    public function sendWelcomeEmail(Entity $entity, SendWelcomeNotificationAction $sendWelcomeNotificationAction): RedirectResponse
    {
        $user = $entity->users()->first();

        if (! $user) {
            return back()->with('error', __('notifications.welcome_email.no_user'));
        }

        $success = $sendWelcomeNotificationAction->execute($user);

        if ($success) {
            return back()->with('success', __('notifications.welcome_email.sent'));
        }

        return back()->with('error', __('notifications.welcome_email.failed'));
    }
}
