<div class="p-6 bg-white rounded-lg shadow">
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">{{ __('licenses.Manage Licenses for') }} {{ $federation->name }}</h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ __('licenses.Select which licenses this federation can offer to its member entities.') }}
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

    {{-- Filters --}}
    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">{{ __('licenses.Search Licenses') }}</label>
            <input type="text" 
                   id="search"
                   wire:model.live.debounce.300ms="searchTerm" 
                   placeholder="{{ __('licenses.Search by name or code...') }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        
        <div>
            <label for="committee" class="block text-sm font-medium text-gray-700 mb-1">{{ __('licenses.Filter by Committee') }}</label>
            <select id="committee"
                    wire:model.live="selectedCommittee" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">{{ __('licenses.All Committees') }}</option>
                @foreach($this->committees as $committee)
                    <option value="{{ $committee->id }}">{{ $committee->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- License Selection --}}
    <form wire:submit="updateLicenses">
        <div class="mb-6 space-y-4 max-h-96 overflow-y-auto border border-gray-200 rounded-lg">
            @forelse($availableLicenses as $committeeName => $licenses)
                <div class="border-b border-gray-200 last:border-b-0">
                    {{-- Committee Header --}}
                    <div class="p-3 bg-gray-50 flex items-center justify-between sticky top-0">
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   wire:click="toggleCommitteeGroup('{{ $committeeName }}')"
                                   @if($this->isGroupSelected($committeeName)) checked @endif
                                   class="h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <h3 class="ml-3 text-sm font-semibold text-gray-900">
                                {{ $committeeName }}
                                <span class="ml-2 text-xs text-gray-500">
                                    ({{ $this->getGroupSelectedCount($committeeName) }}/{{ count($licenses) }} {{ __('licenses.selected') }})
                                </span>
                            </h3>
                        </div>
                    </div>
                    
                    {{-- Licenses in Committee --}}
                    <div class="p-3 space-y-2">
                        @foreach($licenses as $license)
                            <label class="flex items-start space-x-3 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                <input type="checkbox" 
                                       wire:model="selectedLicenses" 
                                       value="{{ $license['id'] }}"
                                       class="mt-0.5 h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $license['name'] }}
                                        @if($license['license_code'])
                                            <span class="ml-2 text-xs text-gray-500">({{ $license['license_code'] }})</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        @if($license['type'])
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ $license['type']['name'] }}
                                            </span>
                                        @endif
                                        @if($license['professional_role'])
                                            <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $license['professional_role']['name'] }}
                                            </span>
                                        @endif
                                        @if($license['sport'])
                                            <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                {{ $license['sport']['name'] }}
                                            </span>
                                        @endif
                                        @if($license['committee']['is_international'] ?? false)
                                            <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                                {{ __('licenses.International') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">
                    @if($searchTerm || $selectedCommittee)
                        {{ __('licenses.No licenses found matching your filters.') }}
                    @else
                        {{ __('licenses.No licenses available.') }}
                    @endif
                </div>
            @endforelse
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-600">
                {{ count($selectedLicenses) }} {{ __('licenses.license(s) selected') }}
            </div>
            
            <div class="flex space-x-3">
                <a href="{{ route('admin.federation.show', $federation) }}" 
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    {{ __('licenses.Cancel') }}
                </a>
                
                <button type="submit" 
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    {{ __('licenses.Save Changes') }}
                </button>
            </div>
        </div>
    </form>
</div>