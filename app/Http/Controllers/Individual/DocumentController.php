<?php

namespace App\Http\Controllers\Individual;

use App\Filters\DocumentDetailOwnerTypeFilter;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentDetail;
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
        $individualOwnerTypes = Document::ownerTypeValuesFor(Individual::class);

        $documents = QueryBuilder::for(Document::class)
            ->allowedFilters([
                AllowedFilter::scope('filter_status'),
                AllowedFilter::scope('type'),
                AllowedFilter::custom('owner_type', new DocumentDetailOwnerTypeFilter),
            ])
            ->with('type', 'owner', 'details', 'moloniInvoice')
            ->whereIn('owner_type', $individualOwnerTypes)
            ->whereIn('owner_id', auth()->user()->individuals->pluck('id'))
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

        $filter_owner_types = Cache::remember('filter_owner_types_'.app()->getLocale(), $ttl, function () {
            return collect(DocumentDetail::getUniqueOwnerTypeOptions())->map(function ($name, $class) {
                return ['id' => $class, 'name' => $name];
            })->values()->all();
        });

        return view('web.individual.document.index', compact('documents', 'filter_status', 'filter_owner_types'));
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
            ->get()
            ->mapWithKeys(function ($method) {
                $translationKey = 'payments.method_' . $method->driver;

                return [$method->id => __($translationKey)];
            })
            ->toArray();

        return view('web.individual.document.show', compact('document', 'relatedDocuments', 'paymentMethods'));
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
