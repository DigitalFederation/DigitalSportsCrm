<div class="card">
    <h3 class="font-bold text-lg text-slate-500 border-b border-slate-500 mb-4 pb-2 flex items-center">
        <x-svg.world class="h-6 w-6 text-slate-600" />
        <span class="ml-2">{{ __('Geography') }}</span>
        <sl-tooltip content="Use these options for filtering the availability of the event.">
            <sl-button>
                <x-svg.info class="h-5 w-5 text-gray-400" />
            </sl-button>
        </sl-tooltip>
    </h3>
    <!-- Geographical Coverage -->

    <div class="w-full mb-2">
        <label class="block text-sm font-medium mb-1"
                for="event_geographical_coverage">{{ __('Geographical Coverage') }} <span
                class="text-rose-500">*</span></label>
        <select name="event_geographical_coverage"
                id="event_geographical_coverage"
                class="form-input w-full {{ $errors->has('event_geographical_coverage') ? 'border-rose-300' : '' }}"
                required>
            <option value="" selected> {{ __('-- Select an option --') }} </option>
            @foreach(\App\Enums\EvtEventGeographicalCoverageEnum::cases() as $scope)
                <option
                    value="{{ $scope->name }}"
                    @if(old('event_geographical_coverage',$event->event_geographical_coverage) == $scope->name) selected @endif
                >{{ $scope->value }}</option>
            @endforeach
        </select>
        <p class="text-xs text-gray-400">{{ __('Indicates if the event is National or Regional.') }}</p>
        @if($errors->has('event_geographical_coverage'))
            <div class="text-xs mt-1 text-rose-500 h-2">
                {{ $errors->first('event_geographical_coverage') }}
            </div>
        @endif
    </div>

    <!-- Portuguese Zones -->
    <div class="w-full">
        <label class="block text-sm font-medium mb-1"
               for="zone_id">{{ __('Zona') }}</label>
        <livewire:input.select-multiple
            :inputSelected="$event->zones?->pluck('id')->toArray()"
            identifier="zones"
            :items="$zones ?? []"
            inputId="zone_id"
            inputName="selected_zones[]" />
        <p class="text-xs text-gray-400">{{ __('Select the Portuguese zones for this event') }}</p>

        @if($errors->has('selected_zones'))
            <div class="text-xs mt-1 text-rose-500 h-2">
                {{ $errors->first('selected_zones') }}
            </div>
        @endif
    </div>

    <!-- Portuguese Districts -->
    <div class="w-full">
        <label class="block text-sm font-medium mb-1" for="district_id">{{ __('Distrito') }}</label>
        <livewire:input.select-multiple
            :inputSelected="$event->districts?->pluck('id')->toArray()"
            identifier="districts"
            :items="$districts ?? []"
            inputId="district_id"
            inputName="selected_districts[]" />
        <p class="text-xs text-gray-400">{{ __('Select the Portuguese districts for this event') }}</p>

        @if($errors->has('selected_districts'))
            <div class="text-xs mt-1 text-rose-500 h-2">
                {{ $errors->first('selected_districts') }}
            </div>
        @endif
    </div>

</div>
