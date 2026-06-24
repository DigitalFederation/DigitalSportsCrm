<div class="w-full card h-full relative overflow-hidden">

    <div class="md:flex justify-between mb-6 mx-auto w-full items-start">
        <ul>
            <li class="text-sm"><strong>{{ __('documents.number_label') }}:</strong>#{{ $document->number_extended }}</li>
            <li class="text-sm"><strong>{{ __('documents.type_label') }}:</strong> {{ $document->type->name }}</li>
            <li class="text-sm">
                <strong>{{ __('documents.date_label') }}:</strong> {{ \Carbon\Carbon::parse($document->created_at)->format('d-m-Y') }}</li>
            <li class="text-sm">
                <strong>{{ __('documents.recipient') }}:</strong>
                {{ $document->getOrganizationName() }}
            </li>
            @if(!empty($document->tax_number))
                <li class="text-sm">
                    <strong>{{ __('documents.vat_number') }}:</strong>
                    {{ $document->getVatNumber() }}
                </li>
            @endif
        </ul>
        <ul>
            @if(!empty($document->customer_city))
                <li class="text-sm">
                    <strong>{{ __('documents.city') }}:</strong>
                    {{ $document->getCity() }}
                </li>
            @endif
            @if(!empty($document->customer_address))
                <li class="text-sm">
                    <strong>{{ __('documents.address') }}:</strong>
                    {{ $document->getAddress() }}
                </li>
            @endif
            @if(!empty($document->customer_postal_code))
                <li class="text-sm">
                    <strong>{{ __('documents.postal_code') }}:</strong>
                    {{ $document->getPostalCode() }}
                </li>
            @endif
            @if(!empty($document->customer_country))
                <li class="text-sm">
                    <strong>{{ __('documents.country') }}:</strong>
                    {{ $document->getCountry() }}
                </li>
            @endif

        </ul>
        <div class="text-right">
            <div class="absolute right-0 top-0 h-16 w-16">
                <div
                    class="absolute transform rotate-45 bg-{{ $document->stateColor() }} text-center text-white font-semibold py-1 right-[-40px] top-[30px] w-[170px]">
                    {{ __('documents.states.' . $document->stateName()) }}
                </div>
            </div>
        </div>

    </div>

    <div class="w-full mx-auto">
        <table class="table table_list w-full">
            <thead class="border-b-slate-300 border-b">
            <tr>
                <th class="w-3/5 text-left uppercase text-xs">{{ __('documents.product') }}</th>
                <th class="uppercase text-right text-xs">{{ __('documents.qty') }}</th>
                <th class="uppercase text-right text-xs">{{ __('documents.unit_price') }}</th>
                <th class="uppercase text-right text-xs">{{ __('documents.amount') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($document->details as $detail)
                <tr class="border-b-slate-200 border-b">
                    <td class="py-3">
                        <p>{{ $detail->enhanced_description }}</p>
                    </td>
                    <td class="py-3 text-right">{{ $detail->quantity }}</td>
                    <td class="py-3 text-right">{{ $detail->unit_value }}€</td>
                    <td class="py-3 text-right font-bold">{{ $detail->total_value }}€</td>
                </tr>
            @endforeach
            </tbody>
            <tfooter>
                <tr>
                    <th colspan="3" class="text-right text-sm uppercase">{{ __('documents.subtotal') }}:</th>
                    <th class="text-right">{{ number_format($document->total_value, 2) }}€</th>
                </tr>

                @if($document->amount_paid > 0)
                    <tr>
                        <th colspan="3" class="text-right text-sm  uppercase">{{ __('documents.amount_paid') }}:</th>
                        <th class="text-right">{{ number_format($document->amount_paid, 2) }}€</th>
                    </tr>
                    <tr>
                        <th colspan="3" class="text-right text-sm  uppercase">{{ __('documents.remaining_balance') }}:</th>
                        <th class="text-right">{{ number_format($document->total_value - $document->amount_paid, 2)}}€
                        </th>
                    </tr>
                @endif
            </tfooter>
        </table>
    </div>

</div>
