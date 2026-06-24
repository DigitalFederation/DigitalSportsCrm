<?php

namespace App\Http\Controllers\Admin;

use App\Exports\CollectionExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\FederationCreateRequest;
use App\Http\Requests\FederationEditRequest;
use App\Models\Committee;
use App\Models\Country;
use App\Models\GeoZone;
use App\Models\SubRegion;
use Carbon\Carbon;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Certifications\States\ExpiredCertificationAttributedState;
use Domain\Certifications\States\PendingCertificationAttributedState;
use Domain\Documents\Actions\CalculateInvoiceAccountSummaryAction;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentType;
use Domain\Documents\States\DraftDocumentState;
use Domain\Federations\Actions\AssociateUserToFederationAction;
use Domain\Federations\Actions\CreateFederationAction;
use Domain\Federations\Actions\DeleteFederationAction;
use Domain\Federations\Actions\EditFederationAction;
use Domain\Federations\DataTransferObject\FederationData;
use Domain\Federations\Models\Federation;
use Domain\Federations\Models\FederationVotingRight;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Memberships\Models\MembershipPlan;
use Domain\Users\Actions\CreateUserAction;
use Domain\Users\Actions\SendWelcomeNotificationAction;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FederationController extends Controller
{
    use InteractsWithMedia;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        // Get the requested is_local filter value
        $isLocal = request()->input('filter.filter_is_local', false); // Default to false
        $isLocal = $isLocal === '' ? false : (bool) $isLocal;

        // --- Prepare Voting Rights Filter Data --- Start ---
        $votingCategories = [
            'general_assembly_status' => 'General Assembly',
            'technical_committee_status' => 'Technical',
            'scientific_committee_status' => 'Scientific',
            'sport_committee_status' => 'Sport',
            'finswimming_commission_status' => 'Finswimming',
            'freediving_commission_status' => 'Freediving',
            'aquathlon_commission_status' => 'Aquathlon',
            'underwater_hockey_commission_status' => 'UW Hockey',
            'underwater_rugby_commission_status' => 'UW Rugby',
            'target_shooting_commission_status' => 'Target Shooting',
            'sport_diving_commission_status' => 'Sport Diving',
            'spearfishing_commission_status' => 'Spearfishing',
            'orienteering_commission_status' => 'Orienteering',
            'visual_commission_status' => 'Visual',
        ];
        $votingStatuses = defined(FederationVotingRight::class . '::STATUS_OPTIONS') ? FederationVotingRight::STATUS_OPTIONS : [];
        $currentYear = Carbon::now()->year;
        // --- Prepare Voting Rights Filter Data --- End ---

        // Define the base query without voting rights filters
        $query = Federation::query()
            ->where('is_local', $isLocal)
            ->orderBy('member_code')
            ->with('country.geoZone', 'country.subRegion');

        // Apply standard filters if they exist in the request
        if (request()->has('filter.filter_name') && request()->input('filter.filter_name') !== null) {
            $query->filterName((string) request()->input('filter.filter_name'));
        }

        if (request()->has('filter.filter_country') && request()->input('filter.filter_country') !== null) {
            $query->filterCountry((int) request()->input('filter.filter_country'));
        }

        if (request()->has('filter.filter_code') && request()->input('filter.filter_code') !== null) {
            $query->filterCode((string) request()->input('filter.filter_code'));
        }

        if (request()->has('filter.filter_committee') && request()->input('filter.filter_committee') !== null) {
            $query->filterCommittee((int) request()->input('filter.filter_committee'));
        }

        if (request()->has('filter.filter_zone') && request()->input('filter.filter_zone') !== null) {
            $query->filterZone((int) request()->input('filter.filter_zone'));
        }

        if (request()->has('filter.filter_region') && request()->input('filter.filter_region') !== null) {
            $query->filterRegion((int) request()->input('filter.filter_region'));
        }

        if (request()->has('filter.filter_membership_plan') && request()->input('filter.filter_membership_plan') !== null) {
            $query->filterMembershipPlan((int) request()->input('filter.filter_membership_plan'));
        }

        // Handle voting rights filter - searching through all parameters since the parameter names appear to be malformed
        $categoryKey = null;
        $status = null;

        // Inspect all parameters to find the ones we need
        foreach (request()->input('filter', []) as $key => $value) {
            // Look for parameters that start with 'filter_voting_right[category_key'
            if (strpos($key, 'filter_voting_right[category_key') === 0 && ! empty($value)) {
                $categoryKey = $value;
            }

            // Look for parameters that start with 'filter_voting_right[status'
            if (strpos($key, 'filter_voting_right[status') === 0 && $value !== null && $value !== '') {
                $status = $value;
            }
        }

        // Debug the extracted values
        Log::info('Voting Rights Filter - Extracted Values', [
            'category_key' => $categoryKey,
            'status' => $status,
            'all_filter_params' => request()->input('filter', []),
        ]);

        if (! empty($categoryKey) && $status !== null && $status !== '') {
            // If the status is numeric, it's an index in the STATUS_OPTIONS array
            // Convert it to the actual string value expected in the database
            if (is_numeric($status)) {
                $statusIndex = (int) $status;
                if (isset(FederationVotingRight::STATUS_OPTIONS[$statusIndex])) {
                    $statusValue = FederationVotingRight::STATUS_OPTIONS[$statusIndex];
                } else {
                    $statusValue = $status; // Fallback to original value
                }
            } else {
                $statusValue = $status;
            }

            Log::info('Applying voting rights filter', [
                'year' => $currentYear,
                'category_key' => $categoryKey,
                'original_status' => $status,
                'mapped_status' => $statusValue,
            ]);

            $query->whereHas('votingRights', function (Builder $subQuery) use ($currentYear, $categoryKey, $statusValue) {
                $subQuery->where('year', $currentYear)
                    ->where($categoryKey, $statusValue);
            });

            // Clone the query for debugging and get the SQL with bindings
            $queryClone = clone $query;
            $queryClone->whereHas('votingRights', function (Builder $subQuery) use ($currentYear, $categoryKey, $statusValue) {
                $subQuery->where('year', $currentYear)
                    ->where($categoryKey, $statusValue);
            });

            // Get the SQL for debugging
            $sql = $queryClone->toSql();
            $bindings = $queryClone->getBindings();

            Log::info('Generated SQL query', [
                'sql' => $sql,
                'bindings' => $bindings,
            ]);
        } else {
            Log::info('Not applying voting rights filter - empty values');
        }

        // Apply pagination and append query parameters
        $federations = $query->paginate(150)
            ->appends(request()->query());

        $countries = Country::select('id', 'name')->orderBy('name')->get();
        $committees = Committee::select('id', 'name')->orderBy('name')->get();
        $cmasZones = GeoZone::select('id', 'name')->orderBy('name')->get();
        $subRegions = SubRegion::select('id', 'name')->orderBy('name')->get();

        // Fetch membership plans that are associated with at least one membership
        $membershipPlans = MembershipPlan::whereHas('memberships')->orderBy('name')->pluck('name', 'id');

        // Set the title based on what we're showing
        $title = $isLocal ? 'Associations' : 'National Organizations';

        return view('web.admin.federation.index', compact(
            'federations',
            'countries',
            'committees',
            'cmasZones',
            'subRegions',
            'membershipPlans',
            'title',
            'votingCategories',
            'votingStatuses'
        ));
    }

    public function export(): BinaryFileResponse
    {
        $federations = QueryBuilder::for(Federation::class)
            ->allowedFilters([
                AllowedFilter::scope('filter_name'),
                AllowedFilter::scope('filter_country'),
                AllowedFilter::scope('filter_code'),
                AllowedFilter::scope('filter_committee'),
            ])
            ->where('is_local', false) // Only show non-local federations
            ->with('country')->get();

        return Excel::download(new CollectionExport($federations, ['name']), 'federations.xlsx');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $federation = new Federation;
        $countries = Country::select('id', 'name')->orderBy('name')->get();
        $federations = Federation::select('id', 'name')->orderBy('name')->get();
        $zones = \Domain\Geographic\Models\Zone::orderBy('name')->get(); // Temporarily remove active() filter to see all zones

        return view('web.admin.federation.create', compact('countries', 'federations', 'federation', 'zones'));
    }

    public function store(
        FederationCreateRequest $federationCreateRequest,
        CreateFederationAction $createFederationAction,
        CreateUserAction $createUserAction,
        AssociateUserToFederationAction $associateUserToFederation
    ): RedirectResponse {
        try {
            DB::beginTransaction();

            $validated = FederationData::fromArray($federationCreateRequest->validated());
            $zoneIds = $federationCreateRequest->validated()['zone_ids'] ?? [];

            $federation = $createFederationAction($validated, $zoneIds);
            if (! $federation) {
                throw new ModelNotFoundException('Federation creation process failed.');
            }

            // Create the USER
            $createUserResult = $createUserAction([
                'email' => $federationCreateRequest->validated()['user_email'],
                'name' => $federationCreateRequest->validated()['member_code'] ?? $federationCreateRequest->validated()['name'],
                'role' => 'FEDERATION',
                'active' => true,
                'bypass_verification' => true,
            ]);
            $user = $createUserResult['user'];

            // Associate USER to Federation if created
            $associateUserToFederation($user, $federation, $federation->is_local ? 'association-territorial-admin' : 'federation-admin');

            DB::commit();

            return redirect(route('admin.federation.index'))->with('success', 'Federation created with success.');
        } catch (ModelNotFoundException $ex) {
            DB::rollBack();
            Log::error($ex->getMessage());

            return back()->with('error', 'Error creating this record.');
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error($ex->getCode() . ': ' . $ex->getMessage());

            return back()->with('error', $ex->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Federation $federation): View
    {
        // Eager load memberships with their plans and zones
        $federation->load(['memberships.plans', 'country', 'zones']);

        $licenses = Cache::remember('licensesAttributed' . $federation->id, Carbon::now()->addMinutes(240), function () use ($federation) {
            return LicenseAttributed::with('license', 'license.committee')->federation($federation->id)->get();
        });

        // Certification slots have been removed from the system
        // Direct certification purchasing is now used instead

        $licensesCount = [
            'SPORT' => [
                'individual' => $licenses->where('model_type', 'Domain\Individuals\Models\Individual')
                    ->filter(function ($item) {
                        return $item->license?->committee->code == 'SPORT';
                    })->count(),
                'entity' => $licenses->where('model_type', 'Domain\Entities\Models\Entity')
                    ->filter(function ($item) {
                        return $item->license?->committee->code == 'SPORT';
                    })->count(),
            ],
            'DIVING' => [
                'individual' => $licenses->where('model_type', 'Domain\Individuals\Models\Individual')
                    ->filter(function ($item) {
                        return $item->license?->committee->code == 'DIVING';
                    })->count(),
                'entity' => $licenses->where('model_type', 'Domain\Entities\Models\Entity')
                    ->filter(function ($item) {
                        return $item->license?->committee->code == 'DIVING';
                    })->count(),
            ],
            'SCIENTIFIC' => [
                'individual' => $licenses->where('model_type', 'Domain\Individuals\Models\Individual')
                    ->filter(function ($item) {
                        return $item->license?->committee->code == 'SCIENTIFIC';
                    })->count(),
                'entity' => $licenses->where('model_type', 'Domain\Entities\Models\Entity')
                    ->filter(function ($item) {
                        return $item->license?->committee->code == 'SCIENTIFIC';
                    })->count(),
            ],
        ];
        // get payment id

        $payment_type_id = DocumentType::query()->where('code', 'PAY')->value('id');

        $invoicesQuery = Document::with('type', 'transactions', 'details')
            ->whereIn('owner_type', Document::ownerTypeValuesFor(Federation::class))
            ->where('owner_id', $federation->id)
            ->whereNot('status_class', DraftDocumentState::class);

        $accountSummary = CalculateInvoiceAccountSummaryAction::execute($invoicesQuery);

        $invoices = $invoicesQuery->paginate(15);

        $attachments = $federation->getMedia('media');

        return view('web.admin.federation.show', compact(
            'federation',
            'licensesCount',
            'invoices',
            'accountSummary',
            'attachments',
        ));
    }

    private function getStatusColor(string $statusClass): string
    {
        return match ($statusClass) {
            ActiveCertificationAttributedState::class => '#3b82f6', // Blue
            PendingCertificationAttributedState::class => '#f59e0b', // Amber
            ExpiredCertificationAttributedState::class => '#ef4444', // Red
            default => '#6b7280' // Gray
        };
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Federation $federation): View
    {
        $countries = Country::select('id', 'name')->orderBy('name')->get();
        $federations = Federation::select('id', 'name')->orderBy('name')->get();
        $zones = \Domain\Geographic\Models\Zone::orderBy('name')->get(); // Temporarily remove active() filter to see all zones

        // Load the zones and users relationships
        $federation->load(['zones', 'users']);

        return view('web.admin.federation.edit', compact('federation', 'countries', 'federations', 'zones'));
    }

    public function files(int $id): View
    {
        $federation = Federation::with('memberships', 'memberships.plans', 'country', 'individuals', 'entities', 'media')->findOrFail($id);

        return view('web.admin.federation.files', compact('federation'));
    }

    public function upload(Request $request): RedirectResponse
    {
        $federation = Federation::findOrFail($request->federation_id);

        if ($request->hasFile('attachments')) {
            $this->uploadFile($request, $federation);
        } else {
            return redirect(route('admin.federation.files', $request->federation_id))->with('error', 'No files were uploaded.');
        }

        return redirect(route('admin.federation.files', $request->federation_id))->with('success', 'Federation updated with success.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FederationEditRequest $request, int $id, EditFederationAction $editFederation): RedirectResponse
    {

        try {
            $zoneIds = $request->validated()['zone_ids'] ?? [];
            $federation = $editFederation(FederationData::fromArray($request->validated()), $id, $zoneIds);

            if ($federation) {
                return redirect(route('admin.federation.show', $id))->with('success', 'Federation updated with success.');
            } else {
                Log::error('Federation wasn\'t deleted but there is no errors.');

                return back()->with('error', 'Error updating a federation.');
            }
        } catch (Exception $ex) {
            Log::error($ex->getCode() . ': ' . $ex->getMessage());

            return back()->with('error', 'Error updating the federation, please contact the please contact the administration.');
        }
    }

    /**
     * Show the license management page for a federation.
     */
    public function licenses(Federation $federation): View
    {
        // Check if user has permission to manage federations
        abort_unless(auth()->user()->can('access federations'), 403);

        return view('web.admin.federation.licenses', compact('federation'));
    }

    /**
     * Show the committee management page for a federation.
     */
    public function committees(Federation $federation): View
    {
        // Check if user has permission to manage federations
        abort_unless(auth()->user()->can('access federations'), 403);

        return view('web.admin.federation.committees', compact('federation'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id, DeleteFederationAction $deleteFederationAction): RedirectResponse
    {
        try {
            $deleted = $deleteFederationAction($id);

            if ($deleted) {
                // Log user id that deleted the federation
                Log::info('Federation deleted with success by user: ' . Auth::id());

                return back()->with('success', 'Federation deleted with success.');
            } else {
                Log::error('Federation don\'t was deleted but there is no errors.');

                return back()->with('error', "The federation hasn't been deleted.");
            }
        } catch (Exception $ex) {
            // This federation is referenced in another table.
            if ($ex->getCode() === 801) {
                return back()->with('error', $ex->getMessage());
            } else {
                Log::error($ex->getCode() . ': ' . $ex->getMessage());

                return back()->with('error', 'Error deleting the federation, please contact the please contact the administration.');
            }
        }
    }

    public function sendWelcomeEmail(Federation $federation, SendWelcomeNotificationAction $sendWelcomeNotificationAction): RedirectResponse
    {
        $user = $federation->users()->first();

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
