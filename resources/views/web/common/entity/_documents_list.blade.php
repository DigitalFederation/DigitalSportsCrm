<div class="card mt-6">
    <header class="pb-2 border-b border-slate-100 flex justify-between">
        <h2 class="font-semibold text-slate-800">{{ __('entity.documents_invoices') }}</h2>
    </header>
    <div class="mt-4">
        @if($documents->count() > 0)
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="text-left">{{ __('entity.table_number') }}</th>
                        <th class="text-left">{{ __('entity.table_date') }}</th>
                        <th class="text-left">{{ __('entity.table_type') }}</th>
                        <th class="text-left">{{ __('entity.table_status') }}</th>
                        <th class="text-right">{{ __('entity.table_total') }}</th>
                        <th class="text-center">{{ __('entity.table_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($documents as $document)
                        <tr>
                            <td>{{ $document->invoice_extended ?? $document->number_extended }}</td>
                            <td>{{ optional($document->created_at)->format('d/m/Y') }}</td>
                            <td>{{ $document->type->name ?? '-' }}</td>
                            <td>
                                <span class="px-2 py-1 rounded text-xs {{ $document->stateColor() }}">
                                    {{ $document->stateName() }}
                                </span>
                            </td>
                            <td class="text-right">{{ money($document->total_value, $document->currency) }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.document.show', $document->id) }}" class="btn btn-xs btn-outline">{{ __('entity.view') }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="text-center text-gray-500 py-4">
                {{ __('entity.no_documents_found') }}
            </div>
        @endif
    </div>
</div>
