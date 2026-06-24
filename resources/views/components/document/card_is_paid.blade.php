<div class="flex information-box items-center w-full mb-4">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.5" stroke="#9e9e9e" fill="none" stroke-linecap="round" stroke-linejoin="round">
        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
        <circle cx="12" cy="12" r="9"/>
        <line x1="12" y1="8" x2="12.01" y2="8"/>
        <polyline points="11 12 12 12 12 16 13 16"/>
    </svg>
    <p class="text-sm">
        {{ __('documents.document_is_paid') }} <br>
        {{ __('documents.find_details_below') }}
    </p>
</div>

<div class="mt-4">

    <div class="text-sm mb-2">
        <span class="font-bold ">{{ __('documents.document_type') }}:</span> {{ $document->type->name }}
    </div>
    <div class="text-sm mb-2">
        <span class="font-bold ">{{ __('documents.created_at') }}:</span> {{ date('d/m/Y', strtotime($document->created_at)) }}
    </div>

    @if($document->moloniInvoice)
        @php
            $namespace = Request::segment(1);
            $moloniRouteName = match($namespace) {
                'entity' => 'entity.document.moloni-pdf',
                'individual' => 'individual.document.moloni-pdf',
                'federation' => 'federation.document.moloni-pdf',
                default => 'admin.document.moloni-pdf',
            };
        @endphp
        <div class="mt-3 mb-2">
            <a href="{{ route($moloniRouteName, $document->id) }}"
               target="_blank"
               class="inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-800 font-medium">
                <x-svg.receipt class="h-4 w-4" />
                {{ __('documents.view_moloni_invoice') }}
                <span class="text-xs text-gray-400">({{ $document->moloniInvoice->moloni_number }})</span>
            </a>
        </div>
    @endif

    @if(!empty($document->transactions) && count($document->transactions) > 0)
        <div class="font-bold text-sm mb-2"> {{ __('documents.transactions') }}</div>

        @foreach($document->transactions as $transaction)
        <div class="flex flex-row gap-x-4 gap-y-2 w-full">
            <div class="flex flex-col text-xs"><span>{{ __('documents.transaction_status') }}</span> <span class="font-bold">{{ $transaction->status }}</span></div>
            <div class="flex flex-col text-xs"><span>{{ __('documents.transaction_date') }}</span> <span class="font-bold">{{ date('d/m/Y H:m', strtotime($transaction->created_at)) }}</span></div>
            @if(!empty($transaction->comment)) <div class="flex flex-col text-xs"><span>{{ __('documents.transaction_info') }}</span> <span class="font-bold">{{ $transaction->comment }}</span></div> @endif
        </div>
        @endforeach


    @endif

    @php //TODO: check if this is needed @endphp
    @if(!empty($relatedDocuments) && count($relatedDocuments) > 0 && 1 == 2)
        <div class="font-bold text-sm mb-2"> {{ __('documents.associated_documents') }}</div>

        @foreach($relatedDocuments as $document)
        <div class="flex justify-between align-middle border-b border-gray-200 py-2">
            <a href="{{ route('admin.document.show', $document->id) }}" class="flex align-middle">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                <span class="ml-2 text-sm">#{{ $document->number_extended }}</span>
            </a>

            <span class="ml-2 text-sm">{{ __('documents.date_label') }}: {{ date('d/m/Y', strtotime($document->created_at)) }}</span>
        </div>
        @endforeach

    @endif

</div>
