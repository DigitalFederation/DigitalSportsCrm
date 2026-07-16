<?php

namespace App\Http\Controllers\Entity;

use App\Filters\DocumentDetailOwnerTypeFilter;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentDetail;
use Domain\Entities\Models\Entity;
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
        $entityId = auth()->user()->entities->pluck('id');
        $ttl = now()->addMinutes(60); // Caches for 1 hour
        $entityOwnerTypes = Document::ownerTypeValuesFor(Entity::class);
        $documents = QueryBuilder::for(Document::class)
            ->allowedFilters([
                AllowedFilter::scope('filter_status'),
                AllowedFilter::scope('type'),
                AllowedFilter::custom('owner_type', new DocumentDetailOwnerTypeFilter),
            ])
            ->with('type', 'owner', 'details', 'moloniInvoice')
            ->where(function ($query) use ($entityId, $entityOwnerTypes) {
                $query->where(function ($q) use ($entityId, $entityOwnerTypes) {
                    // Documents where the entity is the direct owner
                    // Support both morph alias and legacy full class names
                    $q->whereIn('owner_id', $entityId)
                        ->whereIn('owner_type', $entityOwnerTypes);
                });
            })
            ->latest()
            ->paginate(50)
            ->appends(request()->query());

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

        $filter_owner_types = Cache::remember('filter_owner_types_entity', $ttl, function () {
            return collect(DocumentDetail::getUniqueOwnerTypeOptions())->map(function ($name, $class) {
                return ['id' => $class, 'name' => $name];
            })->values()->all();
        });

        return view('web.entity.document.index', compact('documents', 'filter_status', 'filter_owner_types'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View
    {
        $document = Document::with('type', 'details.owner', 'transactions', 'moloniInvoice')->where(compact('id'))->firstOrFail();

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
            })
            ->toArray();

        return view('web.entity.document.show', compact('document', 'relatedDocuments', 'paymentMethods'));
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

        $filename = $document->stateName() != 'paid' ? 'order_' . $document->number_extended . '.pdf' : 'invoice_' . $document->invoice_extended . '.pdf';

        return $pdf->download($filename);
    }
}
