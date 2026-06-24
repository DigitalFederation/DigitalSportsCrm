<div class="p-6 bg-white rounded-lg shadow">
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">{{ __('federation.manage_committees') }} - {{ $federation->name }}</h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ __('Select which committees this federation can manage. Committees determine which certifications and licenses the federation can access.') }}
        </p>
    </div>

    {{-- Success Message --}}
    @if($successMessage)
        <div x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => { show = false; @this.clearMessage() }, 3000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform translate-y-0"
             x-transition:leave-end="opacity-0 transform translate-y-2"
             class="mb-4 p-4 bg-green-50 border border-green-200 rounded-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">
                        {{ $successMessage }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Committee Selection --}}
    <form wire:submit="updateCommittees">
        <div class="mb-6 space-y-3">
            {{-- Select All Header --}}
            <div class="p-3 bg-gray-50 rounded-t-lg border border-gray-200 flex items-center justify-between">
                <div class="flex items-center">
                    <input type="checkbox"
                           wire:click="toggleAll"
                           @if(count($selectedCommittees) === count($this->committees)) checked @endif
                           class="h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                    <span class="ml-3 text-sm font-semibold text-gray-900">
                        {{ __('Select All Committees') }}
                    </span>
                </div>
                <span class="text-xs text-gray-500">
                    {{ count($selectedCommittees) }}/{{ count($this->committees) }} {{ __('selected') }}
                </span>
            </div>

            {{-- Committee List --}}
            <div class="border border-gray-200 rounded-b-lg divide-y divide-gray-200">
                @foreach($this->committees as $committee)
                    <label class="flex items-start space-x-4 p-4 hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox"
                               wire:model="selectedCommittees"
                               value="{{ $committee->id }}"
                               class="mt-1 h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                        <div class="flex-1">
                            <div class="flex items-center">
                                <span class="text-sm font-medium text-gray-900">{{ $committee->name }}</span>
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $committee->is_international ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $committee->is_international ? __('International') : __('National') }}
                                </span>
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-medium bg-gray-100 text-gray-600">
                                    {{ $committee->code }}
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                @if($committee->code === 'SPORT')
                                    {{ __('Sport-related certifications and licenses (non-international)') }}
                                @elseif($committee->code === 'DIVINGSERVICES')
                                    {{ __('Diving services certifications and licenses (non-international)') }}
                                @elseif($committee->code === 'DIVING')
                                    {{ __('Diving certifications and licenses (international)') }}
                                @elseif($committee->code === 'SCIENTIFIC')
                                    {{ __('Scientific certifications and licenses (international)') }}
                                @else
                                    {{ $committee->code }}
                                @endif
                            </p>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Info Box --}}
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <strong>{{ __('Note:') }}</strong> {{ __('International committees (DIVING, SCIENTIFIC) are managed by the International Federation. National committees (SPORT, DIVINGSERVICES) are managed by national federations.') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600">
                {{ count($selectedCommittees) }} {{ __('committee(s) assigned') }}
            </div>

            <div class="flex space-x-3">
                <a href="{{ route('admin.federation.show', $federation) }}"
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    {{ __('Cancel') }}
                </a>

                <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    {{ __('Save Changes') }}
                </button>
            </div>
        </div>
    </form>
</div>
