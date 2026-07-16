<?php

namespace App\Http\Controllers\Federation;

use App\Filters\DocumentDetailOwnerTypeFilter;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentDetail;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Domain\Payments\Models\PaymentMethod;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $ttl = now()->addMinutes(60); // Caches for 1 hour
        $federationOwnerTypes = Document::ownerTypeValuesFor(Federation::class);
        $entityOwnerTypes = Document::ownerTypeValuesFor(Entity::class);
        $federationId = auth()->user()->getFederationId();
        $federation = auth()->user()->getFederation();
        $isMainFederation = $federation && $federation->isMainFederation();

        // Check if filtering by specific owner_id (e.g., from entity profile "View All" link)
        $filterOwnerId = request()->input('filter.filter_owner_id');

        $documents = QueryBuilder::for(Document::class)
            ->allowedFilters([
                AllowedFilter::scope('filter_status'),
                AllowedFilter::scope('filter_owner_id'),
                AllowedFilter::scope('type'),
                AllowedFilter::custom('owner_type', new DocumentDetailOwnerTypeFilter),
                AllowedFilter::scope('filter_years'),
                AllowedFilter::scope('filter_number'),
                AllowedFilter::scope('filter_member_code'),
                AllowedFilter::scope('filter_total'),
                AllowedFilter::scope('filter_member_type', 'filterOwnerType'),
                AllowedFilter::callback('filter_date_start', function ($query, $value) {
                    return $query->whereDate('created_at', '>=', $value);
                }),
                AllowedFilter::callback('filter_date_end', function ($query, $value) {
                    return $query->whereDate('created_at', '<=', $value);
                }),
                AllowedFilter::callback('filter_entity', function ($query, $value) {
                    return $query
                        ->whereIn('owner_type', Document::ownerTypeValuesFor(Entity::class))
                        ->where('owner_id', $value);
                }),
                AllowedFilter::callback('filter_member_name', function ($query, $value) {
                    $escaped = addcslashes($value, '%_');

                    return $query->where(function ($q) use ($escaped) {
                        $q->whereHasMorph('owner', [
                            Individual::class,
                        ], function ($query) use ($escaped) {
                            $query->where('name', 'like', '%' . $escaped . '%');
                        })
                            ->orWhereHasMorph('owner', [
                                Entity::class,
                            ], function ($query) use ($escaped) {
                                $query->where('name', 'like', '%' . $escaped . '%');
                            });
                    });
                }),
            ])
            ->with('type', 'owner', 'details', 'transactions', 'moloniInvoice')
            ->where(function ($query) use ($federationOwnerTypes, $entityOwnerTypes, $federationId, $federation, $isMainFederation, $filterOwnerId) {
                // Main federation can see all documents
                if ($isMainFederation) {
                    return;
                }

                // If filtering by specific owner_id, verify access and allow entity documents
                if ($filterOwnerId) {
                    // Check if this is an entity that belongs to the federation
                    $entity = \Domain\Entities\Models\Entity::find($filterOwnerId);
                    if ($entity && $entity->federations()->where('federation.id', $federationId)->exists()) {
                        $query->where('owner_id', $filterOwnerId)
                            ->whereIn('owner_type', $entityOwnerTypes);

                        return;
                    }
                }

                // Default: federation-owned documents OR documents related to
                // CertificationAttributed/LicenseAttributed in DIVING/SCIENTIFIC committees (view-only).
                $query->where(function ($q) use ($federationOwnerTypes) {
                    $q->whereIn('owner_type', $federationOwnerTypes)
                        ->whereIn('owner_id', auth()->user()->federations()->pluck('federation.id')->toArray());
                })->orWhere(function ($q) use ($federation) {
                    if ($federation) {
                        $q->hasDivingOrScientificCertOrLicenseForFederation($federation);

                        return;
                    }

                    $q->whereRaw('1 = 0');
                });
            })
            ->latest()
            ->paginate(50)
            ->appends(request()->query());

        // Precompute the IDs that the federation owns (to flag the others as view-only).
        $federationOwnedIds = $documents->getCollection()
            ->filter(fn (Document $doc) => in_array($doc->owner_type, $federationOwnerTypes, true)
                && in_array($doc->owner_id, auth()->user()->federations()->pluck('federation.id')->toArray(), true))
            ->pluck('id')
            ->all();

        // Attach the readable owner types to each document
        $documents->getCollection()->transform(function (Document $document) use ($isMainFederation, $federationOwnedIds) {
            $document->owner_type_names = $document->details->pluck('readable_owner_type')->unique();
            $document->is_view_only = ! $isMainFederation && ! in_array($document->id, $federationOwnedIds, true);

            return $document;
        });

        $filter_status = [
            'paid' => ['id' => 'paid', 'name' => __('Paid')],
            'draft' => ['id' => 'draft', 'name' => __('Draft')],
            'pending' => ['id' => 'pending', 'name' => __('Pending')],
            'canceled' => ['id' => 'canceled', 'name' => __('Canceled')],
        ];

        $filter_owner_types = Cache::remember('filter_owner_types', $ttl, function () {
            return collect(DocumentDetail::getOwnerTypeOptions())->map(function ($name, $class) {
                return ['id' => $class, 'name' => $name];
            })->values()->all();
        });

        // Add years filter options - from current year down to 2023
        $filter_years = collect(range(2023, date('Y')))
            ->mapWithKeys(fn ($year) => [$year => $year])
            ->toArray();

        $filter_member_types = [
            'entity' => ['id' => 'entity', 'name' => __('documents.entity')],
            'individual' => ['id' => 'individual', 'name' => __('documents.individual')],
        ];

        $entityQuery = Entity::select('id', 'name')->orderBy('name');

        if (! $isMainFederation && $federationId) {
            $entityQuery->whereHas('federations', function ($q) use ($federationId) {
                $q->where('federation.id', $federationId);
            });
        }

        $filter_entities = $entityQuery->get()
            ->mapWithKeys(fn ($entity) => [$entity->id => ['id' => $entity->id, 'name' => $entity->name]])
            ->toArray();

        return view('web.federation.document.index', compact('documents', 'filter_status', 'filter_owner_types', 'filter_years', 'filter_member_types', 'filter_entities'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View
    {
        $document = Document::with('type', 'details.owner', 'transactions')->where(compact('id'))->firstOrFail();

        // Check if the user is authorized to download the document
        $this->authorize('view', $document);

        $relatedDocuments = $document->relatedDocuments()->get();

        // Get payment methods with translated names based on driver
        $paymentMethods = PaymentMethod::where('is_enabled', true)
            ->supportingCurrency()
            ->get()
            ->mapWithKeys(function ($method) {
                $translationKey = 'payments.method_' . $method->driver;

                return [$method->id => __($translationKey)];
            });

        $federation = auth()->user()->getFederation();
        $isMainFederation = $federation && $federation->isMainFederation();
        $federationOwnerTypes = Document::ownerTypeValuesFor(Federation::class);
        $federationIds = auth()->user()->federations()->pluck('federation.id')->toArray();
        $isOwnedByUserFederation = in_array($document->owner_type, $federationOwnerTypes, true)
            && in_array($document->owner_id, $federationIds, true);
        $isViewOnly = ! $isMainFederation && ! $isOwnedByUserFederation;

        return view('web.federation.document.show', compact('document', 'relatedDocuments', 'paymentMethods', 'isViewOnly'));
    }

    public function download(string $id)
    {
        $document = Document::with('type', 'details.owner', 'owner', 'owner.country', 'transactions')
            ->where(compact('id'))
            ->firstOrFail();

        // Check if the user is authorized to download the document
        $this->authorize('download', $document);

        $pdf = PDF::loadView('web.common.documents.document-invoice-pdf', compact('document'))
            ->setPaper('a4', 'portrait');

        $filename = $document->stateName() != 'paid' ? 'order_'.$document->number_extended.'.pdf' : 'invoice_'.$document->invoice_extended.'.pdf';

        return $pdf->download($filename);
    }
}
