<div wire:key="club-registry-{{ $this->getId() }}" class="min-h-screen bg-gray-50">
    <div class="flex flex-col md:flex-row" x-data="{ isSidebarOpen: window.innerWidth >= 768 }"
        x-init="window.addEventListener('resize', () => { if (window.innerWidth >= 768) { isSidebarOpen = true; } })">

        {{-- Sidebar / Filters - Fixed on left for desktop --}}
        <div x-show="isSidebarOpen" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-300" x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="fixed inset-y-0 left-0 transform shadow-xl z-30 md:z-auto md:static md:transform-none w-full md:w-80 lg:w-96 flex-shrink-0 bg-gray-50 border-r border-gray-200 flex flex-col pt-16 md:pt-0">

            {{-- Sidebar Header --}}
            <div class="px-6 py-4 flex-shrink-0 border-b border-gray-200 bg-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">{{ __('public.club_registry.title') }}</h2>
                        <p class="text-sm text-gray-500">{{ __('public.club_registry.subtitle') }}</p>
                    </div>
                    {{-- Close button for mobile --}}
                    <button @click="isSidebarOpen = false" class="md:hidden p-2 text-gray-500 hover:text-gray-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Sidebar Content --}}
            <div class="px-6 py-4 bg-white flex-1 overflow-y-auto">
                {{-- Stats Panel --}}
                <div class="flex space-x-3 mb-6">
                    <div class="flex-1 bg-white rounded-xl overflow-hidden shadow-sm border border-gray-100">
                        <div class="px-4 py-3 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-blue-100">
                            <p class="text-xs font-medium text-blue-800 uppercase tracking-wider">
                                {{ __('public.club_registry.results') }}
                            </p>
                        </div>
                        <div class="px-4 py-3 flex justify-between items-center">
                            <p class="text-2xl font-bold text-gray-800">{{ $this->entities->total() }}</p>
                            <span class="text-sm text-gray-500">{{ __('public.club_registry.clubs') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Filters --}}
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">
                            {{ __('public.diving_locations.filters') }}
                        </h3>
                        <button type="button" wire:click="clearFilters"
                            class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                            {{ __('public.club_registry.clear_filters') }}
                        </button>
                    </div>

                    <div class="space-y-4" x-data="{ nameOpen: true, sportLicenseOpen: false, districtOpen: false, statusOpen: false }">
                        {{-- Name Filter --}}
                        <div class="bg-white rounded-xl overflow-hidden shadow-sm border border-gray-100">
                            <button type="button" @click="nameOpen = !nameOpen"
                                class="w-full px-4 py-3 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-blue-100 flex justify-between items-center text-left">
                                <p class="text-xs font-medium text-blue-800 uppercase tracking-wider">
                                    {{ __('public.club_registry.filters.name') }}
                                </p>
                                <svg class="w-4 h-4 text-blue-600 transform transition-transform duration-200"
                                    :class="{ 'rotate-180': nameOpen }" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="nameOpen" x-collapse class="p-3">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <input type="text" wire:model.live.debounce.300ms="searchName"
                                        placeholder="{{ __('public.club_registry.filters.name_placeholder') }}"
                                        class="pl-10 block w-full rounded-lg border-gray-300 bg-white text-gray-900 placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500 shadow-sm py-2.5 text-sm">
                                </div>
                            </div>
                        </div>

                        {{-- Sport License Filter --}}
                        @if ($this->sportLicenses->isNotEmpty())
                            <div class="bg-white rounded-xl overflow-hidden shadow-sm border border-gray-100">
                                <button type="button" @click="sportLicenseOpen = !sportLicenseOpen"
                                    class="w-full px-4 py-3 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-blue-100 flex justify-between items-center text-left">
                                    <p class="text-xs font-medium text-blue-800 uppercase tracking-wider">
                                        {{ __('public.club_registry.filters.sport_license') }}
                                    </p>
                                    <svg class="w-4 h-4 text-blue-600 transform transition-transform duration-200"
                                        :class="{ 'rotate-180': sportLicenseOpen }" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div x-show="sportLicenseOpen" x-collapse class="p-3">
                                    <select wire:model.live="selectedSportLicense"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                        <option value="">{{ __('public.club_registry.filters.all_sport_licenses') }}</option>
                                        @foreach ($this->sportLicenses as $sportLicense)
                                            <option value="{{ $sportLicense->id }}">{{ $sportLicense->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif

                        {{-- District Filter --}}
                        @if ($this->districts->isNotEmpty())
                            <div class="bg-white rounded-xl overflow-hidden shadow-sm border border-gray-100">
                                <button type="button" @click="districtOpen = !districtOpen"
                                    class="w-full px-4 py-3 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-blue-100 flex justify-between items-center text-left">
                                    <p class="text-xs font-medium text-blue-800 uppercase tracking-wider">
                                        {{ __('public.club_registry.filters.district') }}
                                    </p>
                                    <svg class="w-4 h-4 text-blue-600 transform transition-transform duration-200"
                                        :class="{ 'rotate-180': districtOpen }" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div x-show="districtOpen" x-collapse class="p-3">
                                    <select wire:model.live="selectedDistrict"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                        <option value="">{{ __('public.club_registry.filters.all_districts') }}</option>
                                        @foreach ($this->districts as $district)
                                            <option value="{{ $district->id }}">{{ $district->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif

                        {{-- Status Filter --}}
                        <div class="bg-white rounded-xl overflow-hidden shadow-sm border border-gray-100">
                            <button type="button" @click="statusOpen = !statusOpen"
                                class="w-full px-4 py-3 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-blue-100 flex justify-between items-center text-left">
                                <p class="text-xs font-medium text-blue-800 uppercase tracking-wider">
                                    {{ __('public.club_registry.filters.status') }}
                                </p>
                                <svg class="w-4 h-4 text-blue-600 transform transition-transform duration-200"
                                    :class="{ 'rotate-180': statusOpen }" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="statusOpen" x-collapse class="p-3">
                                <select wire:model.live="selectedStatus"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                    <option value="">{{ __('public.club_registry.filters.all_statuses') }}</option>
                                    @foreach ($this->statusOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Information --}}
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">
                        {{ __('public.club_registry.legend.title') }}
                    </h3>

                    {{-- RNCAS Description --}}
                    <div class="mb-4 p-4 bg-blue-50 rounded-xl border border-blue-100">
                        <p class="text-xs text-gray-600 leading-relaxed mb-2">
                            {{ __('public.club_registry.subtitle_paragraph_1') }}
                        </p>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            {{ __('public.club_registry.subtitle_paragraph_2') }}
                        </p>
                    </div>

                    <hr class="my-4 border-gray-200">

                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                        {{ __('public.club_registry.legend.subtitle') }}
                    </h4>

                    <div class="space-y-3">
                        {{-- Active --}}
                        <div class="flex items-start space-x-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 flex-shrink-0 mt-0.5">
                                {{ __('public.club_registry.status.active') }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            {{ __('public.club_registry.legend.active_description') }}
                        </p>

                        {{-- Expired --}}
                        <div class="flex items-start space-x-2 mt-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 flex-shrink-0 mt-0.5">
                                {{ __('public.club_registry.status.expired') }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            {{ __('public.club_registry.legend.expired_description') }}
                        </p>

                        {{-- Suspended --}}
                        <div class="flex items-start space-x-2 mt-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 flex-shrink-0 mt-0.5">
                                {{ __('public.club_registry.status.suspended') }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            {{ __('public.club_registry.legend.suspended_description') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content Area --}}
        <div class="flex-1 min-h-screen">
            {{-- Mobile Header with Filter Toggle --}}
            <div class="md:hidden sticky top-16 z-20 bg-white border-b border-gray-200 px-4 py-3">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-lg font-bold text-gray-900">{{ __('public.club_registry.title') }}</h1>
                        <p class="text-sm text-gray-500">{{ $this->entities->total() }} {{ __('public.club_registry.clubs') }}</p>
                    </div>
                    <button @click="isSidebarOpen = true"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        {{ __('public.diving_locations.filters') }}
                    </button>
                </div>
            </div>

            {{-- Entities Grid --}}
            <div class="p-4 md:p-6">
                {{-- Loading Indicator --}}
                <div wire:loading.delay wire:target="searchName, selectedSportLicense, selectedDistrict, selectedStatus"
                    class="fixed inset-0 bg-white bg-opacity-75 flex items-center justify-center z-40">
                    <div class="flex flex-col items-center bg-white rounded-lg p-6 shadow-xl">
                        <svg class="animate-spin h-10 w-10 text-blue-600" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span class="mt-3 text-sm font-medium text-gray-900">{{ __('Loading...') }}</span>
                    </div>
                </div>

                @if ($this->entities->count() > 0)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gradient-to-r from-blue-50 to-blue-100">
                                    <tr>
                                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider align-middle">
                                            {{ __('public.club_registry.table.name') }}
                                        </th>
                                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider align-middle">
                                            {{ __('public.club_registry.table.district') }}
                                        </th>
                                        @foreach ($this->sports as $sport)
                                            <th scope="col" class="px-1 py-3 text-center w-10 align-bottom relative"
                                                x-data="{ show: false }" @mouseenter="show = true" @mouseleave="show = false">
                                                <span class="text-xs font-semibold text-blue-800 uppercase tracking-wider cursor-help">
                                                    {{ $sport->translated_abbreviation }}
                                                </span>
                                                <div x-show="show" x-transition.opacity
                                                    class="absolute z-10 top-full mt-1 left-1/2 -translate-x-1/2 px-2.5 py-1.5 text-xs font-semibold text-blue-800 bg-white rounded-md shadow-lg ring-1 ring-blue-100 whitespace-nowrap">
                                                    {{ $sport->translated_name }}
                                                </div>
                                            </th>
                                        @endforeach
                                        <th scope="col" class="px-3 py-3">
                                            <span class="sr-only">{{ __('public.club_registry.table.view_entity') }}</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($this->entities as $entity)
                                        <tr wire:key="entity-{{ $entity->id }}" class="hover:bg-gray-50 transition-colors duration-150">
                                            {{-- Name --}}
                                            <td class="px-3 py-3 min-w-[200px]">
                                                <div class="flex items-center gap-3">
                                                    <div class="h-8 w-8 rounded-full overflow-hidden bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center flex-shrink-0">
                                                        @if ($entity->getFirstMediaUrl('profile', 'thumb'))
                                                            <img src="{{ $entity->getFirstMediaUrl('profile', 'thumb') }}"
                                                                alt="{{ $entity->name }}"
                                                                class="h-full w-full object-cover">
                                                        @else
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                            </svg>
                                                        @endif
                                                    </div>
                                                    <span class="text-sm font-medium text-gray-900">{{ $entity->name }}</span>
                                                </div>
                                            </td>
                                            {{-- District --}}
                                            <td class="px-3 py-3 whitespace-nowrap">
                                                <span class="text-sm text-gray-600">{{ $entity->district?->name ?? $entity->location }}</span>
                                            </td>
                                            {{-- Sport Columns --}}
                                            @foreach ($this->sports as $sport)
                                                @php
                                                    $status = $this->getSportLicenseStatus($entity, $sport->id);
                                                @endphp
                                                <td class="px-1 py-3 text-center">
                                                    @if ($status === 'active')
                                                        <span class="inline-block h-3 w-3 rounded-full bg-green-500" title="{{ __('public.club_registry.status.active') }}"></span>
                                                    @elseif ($status === 'expired')
                                                        <span class="inline-block h-3 w-3 rounded-full bg-amber-500" title="{{ __('public.club_registry.status.expired') }}"></span>
                                                    @elseif ($status === 'suspended')
                                                        <span class="inline-block h-3 w-3 rounded-full bg-red-500" title="{{ __('public.club_registry.status.suspended') }}"></span>
                                                    @else
                                                        <span class="text-gray-300" title="{{ __('public.club_registry.table.no_license') }}">&mdash;</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td class="px-3 py-3 text-center whitespace-nowrap">
                                                @if ($entity->hasActiveValidationPlanAffiliation())
                                                    <a href="{{ route('public.entity.show', $entity) }}"
                                                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors duration-150">
                                                        {{ __('public.club_registry.table.view_entity') }}
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-6">
                        {{ $this->entities->links() }}
                    </div>
                @else
                    {{-- Empty State --}}
                    <div class="text-center py-12">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-1">
                            {{ __('public.club_registry.no_results') }}
                        </h3>
                        <p class="text-sm text-gray-500 max-w-md mx-auto mb-4">
                            {{ __('public.club_registry.no_results_hint') }}
                        </p>
                        <button wire:click="clearFilters"
                            class="inline-flex items-center px-4 py-2 border border-blue-300 text-sm font-medium rounded-lg text-blue-700 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            {{ __('public.club_registry.clear_filters') }}
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Mobile Overlay --}}
    <div x-show="isSidebarOpen" @click="isSidebarOpen = false"
        class="fixed inset-0 bg-gray-900 bg-opacity-50 z-20 md:hidden" x-transition:enter="transition-opacity ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-300" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"></div>
</div>
