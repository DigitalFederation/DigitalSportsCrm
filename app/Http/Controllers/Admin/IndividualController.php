<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndividualCreateRequest;
use App\Http\Requests\IndividualEditRequest;
use App\Http\Requests\UpdateIndividualEmailRequest;
use App\Models\Country;
use Domain\Documents\Models\Document;
use Domain\Entities\Models\Entity;
use Domain\Federations\Actions\UpdateIndividualEmailAction;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Actions\CreateIndividualAction;
use Domain\Individuals\Actions\DeleteIndividualAction;
use Domain\Individuals\Actions\EditIndividualAction;
use Domain\Individuals\DataTransferObject\IndividualData;
use Domain\Individuals\Models\Individual;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Memberships\Services\MemberNumberService;
use Domain\Memberships\States\ActiveAffiliationState;
use Domain\OfficialDocuments\Models\OfficialDocument;
use Domain\Users\Actions\CreateUserAction;
use Domain\Users\Actions\SendWelcomeNotificationAction;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndividualController extends Controller
{
    use InteractsWithMedia;

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $individuals = QueryBuilder::for(Individual::class)
            ->allowedFilters([
                AllowedFilter::scope('filter_name'),
                AllowedFilter::scope('filter_surname'),
                AllowedFilter::scope('filter_country'),
                AllowedFilter::exact('member_number'),
                AllowedFilter::scope('filter_national_affiliation_status', 'filterNationalAffiliationStatus'),
                AllowedFilter::exact('gender'),
                AllowedFilter::scope('filter_entity'),
            ])
            ->withCount(['affiliations as has_active_validation_plan' => function ($q) {
                $q->where('end_date', '>=', now())
                    ->where('status_class', ActiveAffiliationState::class)
                    ->whereHas('federation', fn ($f) => $f->where('is_default_federation', true))
                    ->whereHas('memberSubscription.membershipPackage.affiliationPlans', function ($ap) {
                        $ap->where('is_validation_plan', true);
                    });
            }])
            ->with('country')
            ->latest()
            ->paginate()
            ->appends(request()->query());

        $countries = Country::select('id', 'name')->orderBy('name')->get();

        $affiliationStatuses = [
            'active' => __('individuals.active'),
            'inactive' => __('individuals.inactive'),
        ];

        $genders = [
            'male' => __('individuals.male'),
            'female' => __('individuals.female'),
        ];

        $entities = Entity::select('id', 'name')->orderBy('name')->get();

        return view('web.admin.individual.index', compact('individuals', 'countries', 'affiliationStatuses', 'genders', 'entities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $individual = new Individual;
        $mainFederation = null;
        $federations = Federation::select('id', 'name')->orderBy('name')->get();
        $entities = Entity::select('id', 'name')->orderBy('name')->get();
        $countries = Country::select('id', 'name')->orderBy('name')->get();

        return view('web.admin.individual.create', compact('federations', 'entities', 'countries', 'individual', 'mainFederation'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(
        IndividualCreateRequest $request,
        CreateIndividualAction $createIndividualAction
    ): RedirectResponse {
        try {
            DB::beginTransaction();

            // Use validated data
            $validatedData = $request->validated();

            // Create USER
            $create_user = new CreateUserAction;
            $createUserResult = $create_user([
                'email' => $validatedData['email'],
                'name' => $validatedData['email'], // Assuming name should be email here? Or use a different field?
                'role' => 'INDIVIDUAL',
            ]);
            $user = $createUserResult['user'];

            // Pass the original validated data array
            $individual = $createIndividualAction(IndividualData::fromArray($validatedData, $user->id));

            // Assign member number to the new individual
            $memberNumberService = new MemberNumberService;
            $memberNumberService->assignIndividualMemberNumber($individual);

            DB::commit();

            if (! empty($individual)) {
                return redirect(route('admin.individual.index'))->with('success', 'Individual created with success.');
            } else {
                DB::rollBack();
                Log::error('Individual wasn\'t created: ' . json_encode($validatedData));

                return back()->with('error', 'Error creating this record.');
            }
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex->getMessage());

            return back()->with('error', $ex->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View|RedirectResponse
    {
        $individual = Individual::with([
            'user',
            'country',
            'certificationsDivingAttributed',
            'certificationsScientificAttributed',
            'certificationsSportAttributed',
            'individualFederations',
            'individualFederations' => function ($query) {
                $query->with('federation.country');
            },
            'individualEntities.entity',
            'licenses',
            // Load active member subscriptions with insurances and affiliations
            'memberSubscriptions' => function ($query) {
                $query->whereIn('status_class', [
                    'Domain\Memberships\States\ActiveMemberSubscriptionState',
                    'Domain\Memberships\States\PendingPaymentMemberSubscriptionState',
                ])->with([
                    'membershipPackage',
                    'insurances.insurancePlan',
                    'affiliations.federation',
                    'affiliations.memberSubscription.membershipPackage.affiliationPlans',
                ]);
            },
            // Load diving-specific relationships
            'divingProfessionalCertifications',
            'divingTechnicalDirectorAssignments.entity',
            'divingTechnicalDirectorAssignments.licenseAttributed.license',
        ])->where(compact('id'))->first();

        if (empty($individual)) {
            return redirect(route('admin.individual.index'))->with('error', 'Error finding the individual.');
        }

        $official_documents = OfficialDocument::with('media')
            ->has('media')
            ->where('individual_id', $individual->id)
            ->latest()
            ->get();

        // Load payment documents for the individual
        $payment_documents = Document::whereIn('owner_type', Document::ownerTypeValuesFor(Individual::class))
            ->where('owner_id', $individual->id)
            ->with(['transactions', 'type', 'method'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('web.common.individual.show', [
            'individual' => $individual,
            'official_documents' => $official_documents,
            'payment_documents' => $payment_documents,
            'context' => 'admin',
        ]);
    }

    public function files(string $id): View
    {
        $individual = Individual::findOrFail($id);
        $official_documents = OfficialDocument::with('media')->where('individual_id', $individual->id)->latest()->get();

        $files = $official_documents->map(function ($official_document) {
            return $official_document->getMedia('media');
        });

        $official_document_types = \App\Enums\OfficialDocumentTypeEnum::individualTypes();

        $countries = Country::select('id', 'name')
            ->whereHas('federations', function (Builder $query) {
                $query->Has('memberships');
            })
            ->orderBy('name')->get();

        return view('web.common.official_documents.files', compact('individual', 'official_documents', 'official_document_types', 'files', 'countries'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $individual = Individual::with(['professionalRoles', 'user'])->where('id', $id)->firstOrFail();
        $federation = $individual->federations()->first(); // Get the first federation associated with the individual

        if ($federation) {
            // Check if federation is available before accessing parent_id
            $mainFederation = Federation::where('id', $federation->parent_id)->first()?->id;

            if ($mainFederation == null) {
                $mainFederation = $federation->id;
                $localFederation = $mainFederation;
            } else {
                $localFederation = $federation->id;
            }
        } else {
            // Handle the case where no federation is associated
            $mainFederation = null;
            $localFederation = null;
        }

        $federations = Federation::select('id', 'name')->orderBy('name')->get();
        $entities = Entity::select('id', 'name')->orderBy('name')->get();
        $countries = Country::select('id', 'name')->orderBy('name')->get();

        // Professional Roles
        $professionalRoles = ProfessionalRole::whereNull('committee_id')->select('id', 'name')->orderBy('name')->pluck('name', 'id');

        return view('web.admin.individual.edit', compact('individual', 'mainFederation', 'localFederation', 'federations', 'entities', 'countries', 'professionalRoles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        IndividualEditRequest $individualEditRequest,
        string $id,
        EditIndividualAction $editIndividualAction
    ): RedirectResponse {

        try {
            // Get validated data
            $validatedData = $individualEditRequest->validated();
            // Safely get optional professional_role_ids from validated data
            $professionalRoleIds = $validatedData['professional_role_ids'] ?? [];

            $updated = $editIndividualAction(
                IndividualData::fromArray(
                    array_merge(
                        $validatedData,
                        ['professional_role_ids' => $professionalRoleIds] // Ensure it's part of the array passed to fromArray
                    ),
                    Individual::find($id)->user_id
                ),
                $id
            );

            if ($updated) {
                return redirect(route('admin.individual.index'))->with('success', 'Individual updated with success.');
            } else {
                Log::error('Individual wasn\'t deleted but there is no errors.');

                return redirect()->back()->with('error', 'Error updating a individual.');
            }
        } catch (Exception $ex) {
            Log::error($ex->getCode() . ': ' . $ex->getMessage());

            return redirect()->back()->with('error', 'Error updating the individual, please contact the please contact the administration.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, DeleteIndividualAction $delete): RedirectResponse
    {
        try {
            $deleted = $delete($id);

            if ($deleted) {
                return back()->with('success', 'Individual soft deleted successfully.');
            } else {
                return back()->with('error', 'Failed to delete the individual.');
            }
        } catch (Exception $ex) {
            Log::error($ex->getCode() . ': ' . $ex->getMessage());

            return back()->with('error', 'Error deleting the individual: ' . $ex->getMessage());
        }
    }

    public function showUpdateEmailForm(Individual $individual): View
    {
        return view('web.federation.individual.update-email', compact('individual'));
    }

    public function updateEmail(
        UpdateIndividualEmailRequest $request,
        Individual $individual,
        UpdateIndividualEmailAction $action
    ): RedirectResponse {
        // Use validated data
        $validated = $request->validated();
        $publicEmail = $validated['public_email'];
        // Check the validated boolean field, defaulting to false if not present/validated
        $updateLoginEmail = $validated['update_login_email'] ?? false;
        $loginEmail = $updateLoginEmail ? ($validated['login_email'] ?? null) : null;

        if ($action->execute($individual, $publicEmail, $loginEmail)) {
            return redirect()->route('admin.individual.show', $individual)
                ->with('success', 'Email updated successfully.');
        }

        return back()->with('error', 'Unable to update email. Please try again or contact support.');
    }

    public function sendWelcomeEmail(Individual $individual, SendWelcomeNotificationAction $sendWelcomeNotificationAction): RedirectResponse
    {
        $user = $individual->user;

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
