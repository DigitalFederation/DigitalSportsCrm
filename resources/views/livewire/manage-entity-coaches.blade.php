<div>
    @if($sportsWithLicenses && $sportsWithLicenses->isNotEmpty())
        <div class="mb-6">
            <label for="sport-select-coaches" class="block text-sm font-medium text-gray-700 mb-2">
                {{ __('coaches.select_sport') }}
            </label>
            <select
                id="sport-select-coaches"
                wire:model.live="selectedSportId"
                class="w-full md:w-1/3 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
                <option value="">{{ __('coaches.select_sport_placeholder') }}</option>
                @foreach($sportsWithLicenses as $sport)
                    <option value="{{ $sport->id }}">{{ $sport->translated_name }}</option>
                @endforeach
            </select>
            <p class="mt-2 text-sm text-gray-500">
                {{ __('coaches.select_sport_help') }}
            </p>
        </div>

        <div class="mt-6">
            {{ $this->table }}
        </div>
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
                        {{ __('No Active Licenses') }}
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>{{ __('Your entity does not have active licenses for any sport. To invite coaches, you must first obtain an entity license for the desired sport.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Associated Coaches List -->
    @if($this->associatedCoaches && $this->associatedCoaches->count() > 0 && $selectedSportId)
        @php
            $coachesForSport = $this->associatedCoaches->filter(function($coach) {
                return $coach->sport_id == $this->selectedSportId;
            });
        @endphp
        
        @if($coachesForSport->count() > 0)
            <div class="mt-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('coaches.associated_coaches_for_sport') }}</h3>
                <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('main.Member Code') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Name') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Country') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Role') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($coachesForSport as $coach)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $coach->individual->member_code ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $coach->individual->full_name ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div class="flex items-center">
                                                @if($coach->individual?->country)
                                                    <img src="{{ asset('img/flags/' . strtolower($coach->individual->country->iso) . '.svg') }}"
                                                         class="w-5 h-5 mr-2 rounded" alt="{{ $coach->individual->country->name }}">
                                                    {{ $coach->individual->country->name }}
                                                @else
                                                    -
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $coach->professionalRole->name ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button 
                                                wire:click="removeAssociation({{ $coach->id }})"
                                                wire:confirm="{{ __('Are you sure you want to remove this coach association?') }}"
                                                class="text-red-600 hover:text-red-900"
                                            >
                                                {{ __('Remove') }}
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>