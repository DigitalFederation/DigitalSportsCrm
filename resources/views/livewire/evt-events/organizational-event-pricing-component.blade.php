<!-- resources/views/livewire/evt-events/organizational-event-pricing-component.blade.php -->

<div>
    <button class="btn btn-primary" wire:click="$toggle('showModal')">{{ __('events.edit_prices') }}</button>

    <template x-teleport="body">
        <div x-data x-show="$wire.showModal" x-cloak class="fixed z-[9999] inset-0 overflow-y-auto" aria-labelledby="modal-title"
             role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0 relative">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
                     @click="$wire.set('showModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div
                    class="relative z-10 inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:align-middle sm:max-w-4xl sm:w-full sm:p-6">
                @if($errorMessage || $successMessage)
                    <div
                        class="flex gap-4 {{ $errorMessage ? 'bg-red-600' : 'bg-green-600' }} p-4 rounded-md mb-4 items-center mt-2">
                        <div class="w-max">
                            <div class="flex rounded-full text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                     stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                    @if($errorMessage)
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    @endif
                                </svg>
                            </div>
                        </div>
                        <div class="text-sm">
                            <p class="text-white leading-tight">{{ $errorMessage ?: $successMessage }}</p>
                        </div>
                    </div>
                @endif

                <div class="text-xs md:text-sm text-slate-500 border border-slate-300 px-2 py-1 mb-2 mt-2">
                    {{ __('events.pricing_modal_description') }}<br>
                    {{ __('events.pricing_modal_description_2') }}
                </div>

                @foreach($pricingTiers as $index => $tier)
                    <div
                        class="flex flex-col md:flex-row gap-x-2 justify-start md:items-center my-4 border border-slate-300 hover:border-slate-600 p-2">
                        <div class="flex flex-col gap-2">
                            <div class="flex flex-row gap-2">
                                <div>
                                    <label for="price_type_{{ $index }}">{{ __('events.price_type') }}</label>
                                    <select id="price_type_{{ $index }}"
                                            wire:model.live="pricingTiers.{{ $index }}.price_type"
                                            class="form-input w-full">
                                        @foreach(\App\Enums\EvtEventFeeTypeEnum::cases() as $feeType)
                                            <option
                                                value="{{ $feeType->value }}">{{ \App\Enums\EvtEventFeeTypeEnum::toString($feeType->value) }}</option>
                                        @endforeach
                                    </select>
                                    @error("pricingTiers.$index.price_type") <span
                                        class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="start_date_{{ $index }}">{{ __('events.start_date') }}</label>
                                    <input class="form-input w-full" wire:model="pricingTiers.{{ $index }}.start_date"
                                           type="date" id="start_date_{{ $index }}">
                                    @error("pricingTiers.$index.start_date") <span
                                        class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="end_date_{{ $index }}">{{ __('events.end_date') }}</label>
                                    <input class="form-input w-full" wire:model="pricingTiers.{{ $index }}.end_date"
                                           type="date"
                                           id="end_date_{{ $index }}">
                                    @error("pricingTiers.$index.end_date") <span
                                        class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div class="w-fit">
                                    <label for="price_{{ $index }}">{{ __('events.price') }} (€)</label>
                                    <input class="form-input w-fit" wire:model="pricingTiers.{{ $index }}.price"
                                           type="number"
                                           id="price_{{ $index }}" step="0.01">
                                    @error("pricingTiers.$index.price") <span
                                        class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="flex flex-row gap-2">
                                @if ($tier['price_type'] === \App\Enums\EvtEventFeeTypeEnum::PER_DISCIPLINE->value)
                                    <div class="w-1/4">
                                        <label for="discipline_id_{{ $index }}">{{ __('events.discipline') }}</label>
                                        <select id="discipline_id_{{ $index }}"
                                                wire:model="pricingTiers.{{ $index }}.discipline_id"
                                                class="form-input w-full">
                                            <option value="">{{ __('events.select_discipline_placeholder') }}</option>
                                            @foreach($disciplines as $id => $name)
                                                <option value="{{ $id }}"
                                                        @if($id == $tier['discipline_id']) selected @endif>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                        @error("pricingTiers.$index.discipline_id") <span
                                            class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                @endif

                                <div class="w-1/4">
                                    <label for="enrollment_role_{{ $index }}">{{ __('events.enrollment_role') }}</label>
                                    <select id="enrollment_role_{{ $index }}"
                                            wire:model="pricingTiers.{{ $index }}.enrollment_role"
                                            class="form-input w-full">
                                        <option value="">{{ __('events.select_role_placeholder') }}</option>
                                        @foreach($roles as $role)
                                            <option
                                                @if($role['value'] == $tier['enrollment_role']) selected @endif
                                            value="{{ $role['name'] }}">{{ \App\Enums\EvtEventEnrollmentRoleEnum::toString($role['value']) }}</option>
                                        @endforeach
                                    </select>
                                    @error("pricingTiers.$index.enrollment_role") <span
                                        class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div class="w-2/4">
                                    <label for="description_{{ $index }}">{{ __('events.description') }}</label>
                                    <input class="form-input w-full" wire:model="pricingTiers.{{ $index }}.description"
                                           type="text" id="description_{{ $index }}"
                                           placeholder="{{ __('events.describe_pricing_tier') }}">
                                    @error("pricingTiers.$index.description") <span
                                        class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-danger btn-sm" wire:click="removePricingTier({{ $index }})"
                                onclick="confirm('{{ __('events.confirm_delete_tier') }}')">
                            <x-svg.trash class="h-4 w-4 inline-block" />
                        </button>
                    </div>
                @endforeach

                <div wire:click="addPricingTier" class="text-sm text-blue-800 underline cursor-pointer">
                    + {{ __('events.insert_new_pricing_line') }}
                </div>

                <div class="mt-4 border-slate-300 border-t pt-2">
                    <button type="button" wire:click="savePricing" class="btn btn-primary">{{ __('events.save_changes') }}</button>
                    <button type="button" wire:click="$set('showModal', false)" class="btn btn-info">{{ __('events.close') }}</button>
                </div>
            </div>
        </div>
    </div>
    </template>
</div>

@script
<script>
    $wire.on("closeModalAndReloadPage", () => {
        window.location.reload();
    });
    window.addEventListener("confirm-removal", event => {
        const confirmed = confirm("{{ __('events.confirm_remove_pricing_tier') }}");
        if (confirmed) {
            @this.
            removePricingTier(event.detail.index);
        }
    });
</script>
@endscript
