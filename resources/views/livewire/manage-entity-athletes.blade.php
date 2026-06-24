<div>
    @if($sportsWithLicenses && $sportsWithLicenses->isNotEmpty())
        <div class="mb-6">
            <label for="sport-select" class="block text-sm font-medium text-slate-700 mb-2">
                {{ __('athletes.select_sport') }}
            </label>
            <select 
                id="sport-select"
                wire:model.live="selectedSportId" 
                class="w-full md:w-1/3 rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
                <option value="">{{ __('athletes.select_sport_placeholder') }}</option>
                @foreach($sportsWithLicenses as $sport)
                    <option value="{{ $sport->id }}">{{ $sport->translated_name }}</option>
                @endforeach
            </select>
            <p class="mt-2 text-sm text-slate-500">
                {{ __('athletes.select_sport_help') }}
            </p>
        </div>

        {{ $this->table }}
    @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">
                        {{ __('athletes.no_active_licenses') }}
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>{{ __('athletes.no_active_licenses_desc') }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>