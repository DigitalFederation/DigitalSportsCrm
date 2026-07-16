<div class="card w-full md:w-1/3 mt-4 md:mt-0">
    <div class="flex gap-x-2  items-center border-b border-gray-300 pb-2 mb-4">
        <x-svg.currency-euro class="w-6 h-6 text-slate-600" />
        <span class="font-bold">{{ __('events.event_fees') }}</span>
    </div>
    <div class="flex flex-col gap-y-4 items-baseline">

        <!-- loop into prices -->
        @foreach($event->pricing as $price)

            <div class="grid grid-cols-2 gap-4 first:border-t-0 pt-4 border-t border-slate-200">
                <div class="flex flex-col">
                    <span
                        class="font-bold text-sm">{{ __('events.from_date') }}: </span>
                    <span class="text-slate-400">{{ date('d/m/Y', strtotime($price->start_date)) }}</span>
                </div>
                <div class="flex flex-col">
                    <span
                        class="font-bold text-sm">{{ __('events.closing_date') }}: </span>
                    <span class="text-slate-400">{{ date('d/m/Y', strtotime($price->end_date)) }}</span>
                </div>
                <div class="flex flex-col">
                    <span
                        class="font-bold text-sm">{{ __('events.price') }}: </span>
                    <div class="text-slate-400">
                        <span>{{ money($price->price) }}</span>
                        <span
                            class="text-slate-400 text-xs">{{ \App\Enums\EvtEventFeeTypeEnum::toString($price->price_type) }}</span>
                    </div>

                </div>
                <div class="flex flex-col">
                    <span
                        class="font-bold text-sm">{{ __('events.info') }}: </span>


                    <span
                        class="text-slate-400">{{ $price->description }}</span>
                </div>
            </div>

        @endforeach

    </div>
</div>
