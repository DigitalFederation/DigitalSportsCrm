<div class="card w-full mt-4">

    <div class="flex gap-x-2 items-center justify-between border-b border-gray-300 pb-2 mb-4">
        <div class="flex gap-x-2 items-center">
            <x-svg.currency-euro class="w-6 h-6 text-slate-600" />
            <span class="font-bold">{{ __('events.registration_fees') }}</span>
            <div class="inline-flex gap-2 items-center text-right">
                @if($event->pricing()?->first()?->price_type)
                    <span class="border border-gray-300 rounded-md px-2 font-bold">
                        {{ \App\Enums\EvtEventFeeTypeEnum::toString(strtoupper($event->pricing()->first()->price_type)) }}
                    </span>
                @endif
            </div>
        </div>

        <div>
            <livewire:evt-events.organizational-event-pricing-component :event="$event" />
        </div>
    </div>

    <div class="text-xs text-slate-500 border border-slate-300 rounded px-3 py-2 mb-3">
        <ul class="space-y-0.5">
            <li><span class="font-semibold">{{ __('events.per_person_fee') }}:</span> {{ __('events.fee_legend_per_person') }}</li>
            <li><span class="font-semibold">{{ __('events.per_discipline_fee') }}:</span> {{ __('events.fee_legend_per_discipline') }}</li>
            <li><span class="font-semibold">{{ __('events.flat_fee') }}:</span> {{ __('events.fee_legend_flat_fee') }}</li>
            <li><span class="font-semibold">{{ __('events.event_fee') }}:</span> {{ __('events.fee_legend_event_fee') }}</li>
            <li><span class="font-semibold">{{ __('events.free') }}:</span> {{ __('events.fee_legend_free') }}</li>
        </ul>
    </div>

    @if($event->pricing->count() > 0)
        <x-dynamic-table
            :headers="[__('events.price'), __('events.from_date'), __('common.description'), __('events.closing_date')]">
            @foreach($event->pricing as $pricing)

                <tr class="hover:bg-gray-100">
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                        <div class="gap-2 items-center text-left">
                            <span class="border border-active rounded-md p-2 font-bold">{{ money($pricing->price) }}</span>
                        </div>
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                        <div class="inline-flex gap-2 items-center">
                            {{ isset($pricing->start_date) ? \Carbon\Carbon::parse($pricing->start_date)->format('d/m/Y') : '---' }}
                        </div>
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                        <div class="inline-flex gap-2 items-center">
                            {{ $pricing->description }}
                        </div>
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap break-words w-px">
                        <div class=" gap-2 items-center text-right">
                            {{ isset($pricing->end_date) ? \Carbon\Carbon::parse($pricing->end_date)->format('d/m/Y') : '---' }}
                        </div>
                    </td>


                </tr>
            @endforeach

        </x-dynamic-table>
    @else
        <x-utility.no-data :inCard="true" />
    @endif
</div>
