<div>

    <h3 class="font-bold text-lg text-slate-500 border-b border-slate-500 mb-2 pb-2">{{ __('Competition Event Pricing') }}</h3>

    <div class="text-xs text-slate-500 border border-slate-300 px-2 py-1 mb-2">
        Configure your competition event's pricing by setting different fee tiers for each discipline.
    </div>


    <div class="mb-4">
        <label for="totalPriceOption" class="block text-sm font-medium mb-1">Pricing Option</label>
        <select wire:model.live="pricingOptionsSelected"
                {{ $disablePricingOptions ? 'disabled' : '' }}
                id="totalPriceOption"
                class="form-input w-full">
            <option value="">Choose an option</option>
            @foreach($pricingOptions as $key => $value)
                <option value="{{ $key }}">{{ $value }}</option>
            @endforeach
        </select>
        @if($disablePricingOptions)
            <div class="text-xs text-gray-600">
                Note: Pricing options are locked after initial setup and cannot be changed.
            </div>
        @endif
    </div>


    @if ($pricingOptionsSelected == 'price_per_discipline')

        @foreach($pricingTiers as $index => $tier)
            <div wire:key="pricing-tier-{{ $index }}">
                <div class="mb-4">
                    <div class="flex flex-col gap-x-2 justify-between my-2 bg-slate-100 p-2 rounded-md">

                        <div>
                            <label for="discipline_{{ $index }}">Discipline</label>
                            <select class="form-input w-full" wire:model="pricingTiers.{{ $index }}.discipline_id"
                                    id="discipline_{{ $index }}">
                                <option value="">Select Discipline</option>
                                @foreach($disciplines as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex justify-between gap-x-2 items-end">
                            <div>
                                <label for="start_date_{{ $index }}">Start Date</label>
                                <input class="form-input w-full" wire:model="pricingTiers.{{ $index }}.start_date"
                                       type="date"
                                       id="start_date_{{ $index }}">
                            </div>

                            <div>
                                <label for="end_date_{{ $index }}">End Date</label>
                                <input class="form-input w-full" wire:model="pricingTiers.{{ $index }}.end_date"
                                       type="date"
                                       id="end_date_{{ $index }}">
                            </div>

                            <div>
                                <label for="price_{{ $index }}">Price</label>
                                <input class="form-input w-full" wire:model="pricingTiers.{{ $index }}.price"
                                       type="number"
                                       id="price_{{ $index }}" step="0.01">
                            </div>

                            <div>
                                <label for="price_type_{{ $index }}">Pricing Type</label>
                                <select class="form-input w-full" wire:model="pricingTiers.{{ $index }}.price_type"
                                        id="price_type_{{ $index }}">
                                    <option value="per_person">Per Person</option>
                                    <option value="flat_fee">Per Team</option>
                                </select>
                            </div>

                            <div>
                                <button type="button" wire:click="removePricingTier({{ $index }})"
                                        class="btn-sm btn-info">
                                    <x-svg.trash class="w-4 h-4" />
                                </button>
                            </div>

                        </div>

                        @error("pricingTiers.{$index}.discipline_id")
                        <div class="text-red-600">{{ $message }}</div>
                        @enderror

                    </div>
                </div>
            </div>
        @endforeach

    @elseif($pricingOptionsSelected == 'total_price' || $pricingOptionsSelected == 'price_per_person_unique')

        @foreach($pricingTiers as $index => $tier)
            <div wire:key="pricing-tier-{{ $index }}">
                <div class="mb-4">

                    <div class="flex justify-between gap-x-2 items-end">
                        <div>
                            <label for="start_date_{{ $index }}">Start Date</label>
                            <input class="form-input w-full" wire:model="pricingTiers.{{ $index }}.start_date"
                                   type="date"
                                   id="start_date_{{ $index }}">
                        </div>

                        <div>
                            <label for="end_date_{{ $index }}">End Date</label>
                            <input class="form-input w-full" wire:model="pricingTiers.{{ $index }}.end_date"
                                   type="date"
                                   id="end_date_{{ $index }}">
                        </div>

                        <div>
                            <label for="price_{{ $index }}">Price</label>
                            <input class="form-input w-full" wire:model="pricingTiers.{{ $index }}.price"
                                   type="number"
                                   id="price_{{ $index }}" step="0.01">
                        </div>


                        <div>
                            <button type="button" wire:click="removePricingTier({{ $index }})"
                                    class="btn-sm btn-info">
                                <x-svg.trash class="w-4 h-4" />
                            </button>
                        </div>

                    </div>

                    @error("pricingTiers.{$index}.discipline_id")
                    <div class="text-red-600">{{ $message }}</div>
                    @enderror

                </div>
            </div>
        @endforeach

    @endif


    @if($errorMessage)
        <div class="font-bold py-2">{{ $errorMessage }}</div>
    @endif


    <div class="mt-4 border-t border-slate-500 pt-4">
        <button type="button" wire:click="addPricingTier" class="btn-sm btn-info">Add Pricing line</button>
        <button type="button" wire:click="savePricing" class="btn-sm btn-info">Save Pricing</button>
    </div>

</div>
