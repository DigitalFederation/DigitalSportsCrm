<div>
    {{-- Medals Section --}}
    <h4 class="text-sm font-medium text-slate-600 mb-2">{{ __('events.form.medals') }}</h4>
    <div class="flex flex-col md:flex-row w-full gap-4">
        <div class="w-1/3">
            <label for="medals_gold" class="block text-sm font-medium mb-1">{{ __('events.form.medals_gold') }}</label>
            <input type="number" min="0"
                   id="medals_gold"
                   name="competition[medals_gold]"
                   value="{{ old('competition.medals_gold', optional($event->competition)->medals_gold) }}"
                   class="form-input w-full">
            @error('competition.medals_gold') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        <div class="w-1/3">
            <label for="medals_silver" class="block text-sm font-medium mb-1">{{ __('events.form.medals_silver') }}</label>
            <input type="number" min="0"
                   id="medals_silver"
                   name="competition[medals_silver]"
                   value="{{ old('competition.medals_silver', optional($event->competition)->medals_silver) }}"
                   class="form-input w-full">
            @error('competition.medals_silver') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        <div class="w-1/3">
            <label for="medals_bronze" class="block text-sm font-medium mb-1">{{ __('events.form.medals_bronze') }}</label>
            <input type="number" min="0"
                   id="medals_bronze"
                   name="competition[medals_bronze]"
                   value="{{ old('competition.medals_bronze', optional($event->competition)->medals_bronze) }}"
                   class="form-input w-full">
            @error('competition.medals_bronze') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
    </div>

    {{-- Trophies Section --}}
    <h4 class="text-sm font-medium text-slate-600 mb-2 mt-6">{{ __('events.form.trophies') }}</h4>
    <div class="flex flex-col md:flex-row w-full gap-4">
        <div class="w-1/3">
            <label for="trophies_first" class="block text-sm font-medium mb-1">{{ __('events.form.trophies_first') }}</label>
            <input type="number" min="0"
                   id="trophies_first"
                   name="competition[trophies_first]"
                   value="{{ old('competition.trophies_first', optional($event->competition)->trophies_first) }}"
                   class="form-input w-full">
            @error('competition.trophies_first') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        <div class="w-1/3">
            <label for="trophies_second" class="block text-sm font-medium mb-1">{{ __('events.form.trophies_second') }}</label>
            <input type="number" min="0"
                   id="trophies_second"
                   name="competition[trophies_second]"
                   value="{{ old('competition.trophies_second', optional($event->competition)->trophies_second) }}"
                   class="form-input w-full">
            @error('competition.trophies_second') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        <div class="w-1/3">
            <label for="trophies_third" class="block text-sm font-medium mb-1">{{ __('events.form.trophies_third') }}</label>
            <input type="number" min="0"
                   id="trophies_third"
                   name="competition[trophies_third]"
                   value="{{ old('competition.trophies_third', optional($event->competition)->trophies_third) }}"
                   class="form-input w-full">
            @error('competition.trophies_third') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
    </div>
</div>
