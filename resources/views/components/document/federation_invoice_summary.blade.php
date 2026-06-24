@if(!empty($documents['invoices']))
    <div class="card mt-6">
        <header class="mb-4">
            <h2 class="font-semibold text-slate-800">{{ __('Account Overview')}}</h2>
        </header>
        <div class="overflow-x-auto">
            <table class="table-auto w-full">
                <thead
                    class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                <tr>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">{{ __('Number') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">{{ __('Date') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">{{ __('Type') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-right">{{ __('Status') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-right">{{ __('Total') }}</div>
                    </th>

                </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-200">
                @foreach($documents['invoices'] as $document)
                    <tr>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            <a href="{{ route('admin.document.show', $document->id) }}">{{ $document->number_extended }}</a>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            {{ \Carbon\Carbon::parse($document->created_at)->format('d-m-Y') }}
                            <small>({{ \Carbon\Carbon::parse($document->created_at)->diffForHumans() }})</small>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">{{ $document->type->name }}</td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-right">{{ ucfirst($document->stateName()) }}</td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-right">
                            @if($document->isCanceled())
                                <span class="text-slate-300 line-through">{{ $document->total_value }}€</span>
                            @else
                                {{ $document->total_value }}€
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot class="border-t border-gray-400">
                <tr>
                    <td colspan="6" class="pt-4">
                        {{ $documents['invoices']->links() }}
                    </td>
                </tr>
                <tr>
                    <th colspan="4">
                    <td class="pt-4 pr-5">
                        <div class="font-semibold text-left flex justify-between">
                            <span>{{ __('Total') }}: </span>
                            <span>{{ $documents['total'] }}€ </span>
                        </div>
                        <div class="font-semibold text-left flex justify-between">
                            <span>{{ __('Total Paid') }}: </span>
                            <span>{{ $documents['total_paid'] }}€ </span>
                        </div>
                        <div class="font-semibold text-left flex justify-between">
                            <span>{{ __('Current Balance') }}: </span>
                            <span class="@if($documents['current_balance'] < 0) text-red-600 @endif">{{ $documents['current_balance'] }}€ </span>
                        </div>
                    </td>
                </tr>
                </th>
                </tfoot>
            </table>
        </div>
    </div>
@endif
