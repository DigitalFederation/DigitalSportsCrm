<?php

namespace App\Http\Controllers\Admin;

use App\Exports\InvoicesExport;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Domain\Documents\Actions\CancelDocumentAction;
use Domain\Documents\Actions\ResendInvoiceNotificationAction;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentDetail;
use Domain\Documents\States\PaidDocumentState;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DocumentController extends Controller
{
    /**
     * Display a listing of Orders only it doesnt return any other document type
     */
    public function index(): View
    {
        $documents = QueryBuilder::for(Document::class)
            ->allowedFilters([
                AllowedFilter::scope('filter_status'),
                AllowedFilter::scope('filter_total'),
                AllowedFilter::scope('filter_federations'),
                AllowedFilter::scope('filter_owner_id'),
                AllowedFilter::scope('type'),
                AllowedFilter::callback('owner_type', function ($query, $value) {
                    if ($value === 'manual_order') {
                        return $query->whereHas('details', function ($query) {
                            $query->whereNull('owner_type')
                                ->orWhere('owner_type', '');
                        });
                    }

                    // Handle other owner types using the existing filter
                    return $query->whereHas('details', function ($query) use ($value) {
                        $query->where('owner_type', $value);
                    });
                }),
                AllowedFilter::scope('filter_years'),
                AllowedFilter::scope('filter_number'),
                AllowedFilter::scope('filter_member_code'),
                AllowedFilter::scope('filter_member_type', 'filterOwnerType'),
                AllowedFilter::callback('filter_date_start', function ($query, $value) {
                    return $query->whereDate('created_at', '>=', $value);
                }),
                AllowedFilter::callback('filter_date_end', function ($query, $value) {
                    return $query->whereDate('created_at', '<=', $value);
                }),
                AllowedFilter::callback('filter_entity', function ($query, $value) {
                    return $query->whereIn('owner_type', [
                        (new Entity)->getMorphClass(),
                        Entity::class,
                    ])
                        ->where('owner_id', $value);
                }),
                AllowedFilter::callback('filter_member_name', function ($query, $value) {
                    $escaped = addcslashes($value, '%_');

                    return $query->where(function ($q) use ($escaped) {
                        $q->whereHasMorph('owner', [
                            \Domain\Individuals\Models\Individual::class,
                        ], function ($query) use ($escaped) {
                            $query->where('name', 'like', '%' . $escaped . '%');
                        })
                            ->orWhereHasMorph('owner', [
                                \Domain\Entities\Models\Entity::class,
                            ], function ($query) use ($escaped) {
                                $query->where('name', 'like', '%' . $escaped . '%');
                            });
                    });
                }),
            ])
            ->with('type', 'owner', 'details', 'transactions')
            ->where(function (Builder $query) {
                return $query->whereHas('type', function ($query) {
                    $query->where('code', 'ORD');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(100)
            ->appends(request()->query());

        // Manually load entityFederations for each owner if it's an Entity
        $documents->getCollection()->each(function (Document $document) {
            if ($document->owner instanceof \Domain\Entities\Models\Entity) {
                $document->owner->load('entityFederations');
            }
        });

        // Attach the readable owner types to each document
        $documents->getCollection()->transform(function (Document $document) {
            $document->owner_type_names = $document->details->pluck('readable_owner_type')->unique();

            return $document;
        });

        $filter_status = [
            'paid' => ['id' => 'paid', 'name' => __('documents.states.paid')],
            'draft' => ['id' => 'draft', 'name' => __('documents.states.draft')],
            'pending' => ['id' => 'pending', 'name' => __('documents.states.pending')],
            'canceled' => ['id' => 'canceled', 'name' => __('documents.states.canceled')],
        ];

        $filter_federations = Federation::select('id', 'name')->orderBy('name')->get()->pluck('name', 'id')->toArray();

        $filter_entities = Entity::select('id', 'name')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn ($entity) => [$entity->id => ['id' => $entity->id, 'name' => $entity->name]])
            ->toArray();

        $filter_owner_types = [
            'Domain\\Licenses\\Models\\LicenseAttributed' => ['id' => 'Domain\\Licenses\\Models\\LicenseAttributed', 'name' => __('documents.categories.License')],
            'Domain\\Memberships\\Models\\MemberSubscription' => ['id' => 'Domain\\Memberships\\Models\\MemberSubscription', 'name' => __('documents.categories.Membership')],
            'Domain\\Certifications\\Models\\CertificationSlot' => ['id' => 'Domain\\Certifications\\Models\\CertificationSlot', 'name' => __('documents.categories.Certification')],
            'Domain\\EvtEvents\\Models\\Enrollment' => ['id' => 'Domain\\EvtEvents\\Models\\Enrollment', 'name' => __('documents.categories.Registration')],
            'Domain\\Insurances\\Models\\Insurance' => ['id' => 'Domain\\Insurances\\Models\\Insurance', 'name' => __('documents.categories.Insurance')],
            'manual_order' => ['id' => 'manual_order', 'name' => __('documents.categories.Manual Order')],
        ];

        // Add years filter options - from current year down to 2023
        $filter_years = collect(range(2023, date('Y')))
            ->mapWithKeys(fn ($year) => [$year => $year])
            ->toArray();

        $filter_member_types = [
            'entity' => ['id' => 'entity', 'name' => __('documents.entity')],
            'individual' => ['id' => 'individual', 'name' => __('documents.individual')],
        ];

        return view('web.admin.document.index', compact('documents', 'filter_status', 'filter_federations', 'filter_entities', 'filter_owner_types', 'filter_years', 'filter_member_types'));
    }

    public function create(): View
    {
        return view('web.admin.document.create');
    }

    /**
     * Edit the specified document.
     *
     * @return View|RedirectResponse
     */
    public function edit(string $id)
    {
        $document = Document::with('type', 'details.owner')->where(compact('id'))->firstOrFail();

        if ($document->status_class == PaidDocumentState::class) {
            return redirect()->route('admin.document.index')
                ->with('error', __('documents.edit_draft_only'));
        }

        return view('web.admin.document.edit', [
            'document' => $document,
        ]);
    }

    /**
     * Display a listing of Invoices.
     */
    public function invoices(Request $request): View
    {
        // Validate request parameters
        $request->validate([
            'filter_date_start' => 'nullable|date',
            'filter_date_end' => 'nullable|date|after_or_equal:filter_date_start',
            'filter_federations' => 'nullable|exists:federations,id',
            'filter_years' => 'nullable|array',
            'filter_years.*' => 'integer|min:max:' . (date('Y') + 1),
            'filter_owner_type' => 'nullable|in:federation,entity,individual,manual',
            'filter_member_code' => 'nullable|string',
        ]);

        // Base query builder with comprehensive filtering
        $documents = QueryBuilder::for(Document::class)
            ->allowedFilters([
                AllowedFilter::scope('filter_years'),
                AllowedFilter::scope('filter_date_start'),
                AllowedFilter::scope('filter_date_end'),
                AllowedFilter::scope('filter_owner_type'),
                AllowedFilter::scope('filter_federations'),
                AllowedFilter::scope('filter_document_type'),
                AllowedFilter::scope('filter_member_code'),
            ]);

        // Add status filter
        $documents->where('status_class', PaidDocumentState::class);

        // Add invoice number filter
        $documents->whereNotNull('invoice_number');

        // Add document type filter - modified to include orders with invoice numbers
        $documents->whereHas('type', function ($query) {
            $query->where('code', 'ORD'); // Changed from 'INV' to 'ORD'
        });

        // Add relationships and ordering
        $documents = $documents->with([
            'type',
            'owner.country',
            'transactions.paymentMethod',
            'details.owner',
            // Modify owner relationship to load all necessary relationships for display name
            'owner' => function ($query) {
                $query->when(
                    $query->getModel() instanceof \Domain\Individuals\Models\Individual,
                    fn ($q) => $q->with([
                        'federations:id,name,member_code',
                        'country:id,name',
                    ])
                )
                    ->when(
                        $query->getModel() instanceof \Domain\Entities\Models\Entity,
                        fn ($q) => $q->with([
                            'entityFederations:id,name,member_code',
                            'country:id,name',
                        ])
                    )
                    ->when(
                        $query->getModel() instanceof \Domain\Federations\Models\Federation,
                        fn ($q) => $q->with(['country:id,name'])
                    );
            },
            // For Memberships
            'details.owner' => function ($query) {
                $query->when(
                    $query->getModel() instanceof \Domain\Memberships\Models\Membership,
                    fn ($q) => $q->with(['plans.licenses'])
                );
            },
        ])
            ->orderBy('invoice_year', 'desc')
            ->orderBy('invoice_number', 'desc');

        // Get the paginated results
        $paginatedDocuments = $documents->paginate(100)
            ->appends(request()->query());

        // Prepare filter options
        $filter_federations = Federation::select('id', 'name')
            ->orderBy('name')
            ->get()
            ->pluck('name', 'id');

        $filter_years = collect(range(date('Y'), 2023))
            ->mapWithKeys(fn ($year) => [$year => $year]);

        $filter_owner_types = Cache::remember('owner_types', now()->addDay(), function () {
            return collect([
                'federation' => __('Federation'),
                'entity' => __('Entity'),
                'individual' => __('Individual'),
                'manual' => __('Manual'),
            ]);
        });

        // Pass the paginated results to the view
        return view('web.admin.document.invoices', compact(
            'paginatedDocuments',
            'filter_federations',
            'filter_years',
            'filter_owner_types'
        ));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View
    {
        $document = Document::with('type', 'details.owner')->where(compact('id'))->firstOrFail();
        $relatedDocuments = $document->relatedDocuments()->get();

        return view('web.admin.document.show', compact('document', 'relatedDocuments'));
    }

    /**
     * Download the specified document.
     */
    public function download(string $id)
    {
        $document = Document::with('type', 'details.owner', 'owner')->where(compact('id'))->firstOrFail();
        // Conditionally load the 'country' relationship if appropriate
        if ($document->owner_type !== 'Domain\Documents\Models\Document') {
            $document->load('owner.country');
        }

        $pdf = PDF::loadView('web.admin.document.pdf', compact('document'))
            ->setPaper('a4', 'portrait')
            ->setOption(['dpi' => 72, 'defaultFont' => 'sans-serif']);

        $filename = 'invoice_' . $document->invoice_extended . '.pdf';

        return $pdf->download($filename);
        // return view('web.admin.document.pdf', compact('document'));
    }

    public function notify(Document $document, ResendInvoiceNotificationAction $action): RedirectResponse
    {
        try {
            $action->execute($document);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());

            return redirect()->back()->with('error', $exception->getMessage());
        }

        return redirect()->back()->with('success', __('documents.notification_sent'));
    }

    public function destroy(string $id)
    {
        $document = Document::findOrFail($id);

        try {
            DB::beginTransaction();

            // Delete document details directly via query builder to bypass model events
            // This is necessary because DocumentDetail has an observer that prevents
            // deleting the last detail of a document
            DocumentDetail::where('document_id', $id)->forceDelete();

            // Delete the document
            $document->delete();

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage());

            return redirect()->back()->with('error', $exception->getMessage());
        }

        return redirect()->route('admin.document.index')->with('success', __('documents.document_deleted_successfully'));
    }

    public function cancel(string $id, Request $request): RedirectResponse
    {
        $document = Document::findOrFail($id);
        $cancelAction = new CancelDocumentAction;

        try {
            $cancelAction->execute($document, $request->input('reason'));

            return redirect()->back()->with('success', __('documents.document_canceled_successfully'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function deleteCanceledDocument(string $documentId): RedirectResponse
    {
        $document = Document::with('transactions')->findOrFail($documentId);

        // Check if the document is in 'Canceled' state
        if (! $document->isCanceled()) {
            return redirect()->back()->withErrors(['message' => __('documents.not_cancellable_state')]);
        }

        // Check if the document has no associated payment transactions
        if ($document->transactions()->exists()) {
            return redirect()->back()->withErrors(['message' => __('documents.has_associated_payments')]);
        }

        // Perform the deletion
        if ($document->details()->exists()) {
            $document->details()->delete();
        }
        // Perform the deletion of the document
        $document->delete();

        return redirect()->route('admin.document.index')->with('success', __('documents.document_deleted_successfully'));
    }

    /**
     * Export all issued invoices as an Excel file.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
     */
    public function exportInvoices(Request $request)
    {
        // Validate export parameters
        $request->validate([
            'filter_date_start' => 'nullable|date',
            'filter_date_end' => 'nullable|date|after_or_equal:filter_date_start',
            'filter_federations' => 'nullable|exists:federations,id',
            'filter_years' => 'nullable|array',
            'filter_years.*' => 'integer|min:2000|max:' . (date('Y') + 1),
            'filter_owner_type' => 'nullable|string',
            'filter_member_code' => 'nullable|string',
        ]);

        // Build the query using Spatie QueryBuilder for consistent filtering
        $queryBuilder = QueryBuilder::for(Document::class)
            ->allowedFilters([
                AllowedFilter::scope('filter_date_start'),
                AllowedFilter::scope('filter_date_end'),
                AllowedFilter::scope('filter_federations'),
                AllowedFilter::scope('filter_years'),
                AllowedFilter::scope('filter_owner_type'),
                AllowedFilter::scope('filter_member_code'),
            ])
            ->with([
                'type',
                'owner.country',
                'details.owner' => function ($morphTo) {
                    $morphTo->morphWith([
                        \Domain\EvtEvents\Models\Enrollment::class => ['enrollable'],
                        \Domain\EvtEvents\Models\IndividualEnrollment::class => [],
                        \Domain\EvtEvents\Models\AthleteEnrollment::class => [],
                    ]);
                },
                'transactions.paymentMethod',
                'owner' => function ($morphTo) {
                    $morphTo->morphWith([
                        \Domain\Entities\Models\Entity::class => ['federations:id,name,member_code,country_id', 'country:id,name'],
                        \Domain\Individuals\Models\Individual::class => ['federations:id,name,member_code'],
                        \Domain\Federations\Models\Federation::class => ['country:id,name'],
                        \Domain\Memberships\Models\Membership::class => ['plans.licenses'],
                        \Domain\Licenses\Models\LicenseAttributed::class => ['license'],
                        'Domain\\Certifications\\Models\\CertificationSlot' => ['certification', 'slotType'],
                        \Domain\EvtEvents\Models\Event::class => [],
                        \Domain\EvtEvents\Models\IndividualEnrollment::class => ['event'],
                        \Domain\EvtEvents\Models\AthleteEnrollment::class => ['event'],
                        \Domain\EvtEvents\Models\Enrollment::class => ['enrollable', 'event'],
                    ]);
                },
            ])
            ->whereNotNull('invoice_number')
            ->where('status_class', PaidDocumentState::class)
            ->whereHas('type', function ($query) {
                $query->where('code', 'ORD');
            });

        // Apply sorting
        $queryBuilder->orderBy('invoice_year', 'desc')
            ->orderBy('invoice_number', 'desc');

        // Get all documents matching the filters (without pagination)
        $documents = $queryBuilder->get();

        // Verify we have data to export
        if ($documents->isEmpty()) {
            return redirect()->back()
                ->with('error', __('documents.no_invoices_found'));
        }

        // Create a unique, descriptive filename
        $fileName = sprintf(
            'invoices_export_%s_%s_%s.xlsx',
            $request->input('filter_date_start', 'all'),
            $request->input('filter_date_end', 'all'),
            now()->format('Ymd_His')
        );

        try {
            // Return the Excel download with a specific chunk size for large datasets
            return Excel::download(
                new InvoicesExport($documents),
                $fileName,
                \Maatwebsite\Excel\Excel::XLSX,
                ['chunk_size' => 1000]
            );
        } catch (\Exception $e) {
            Log::error('Invoice export failed: ' . $e->getMessage(), [
                'filters' => $request->all(),
                'document_count' => $documents->count(),
            ]);

            return redirect()->back()
                ->with('error', __('documents.export_failed'));
        }
    }
}
