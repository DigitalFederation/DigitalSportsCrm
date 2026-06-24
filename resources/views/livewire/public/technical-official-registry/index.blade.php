<div wire:key="technical-official-registry-{{ $this->getId() }}" class="min-h-screen bg-gray-50">
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
                        <h2 class="text-lg font-bold text-gray-900">{{ __('public.technical_official_registry.title') }}</h2>
                        <p class="text-sm text-gray-500">{{ __('public.technical_official_registry.subtitle') }}</p>
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
                                {{ __('public.technical_official_registry.results') }}
                            </p>
                        </div>
                        <div class="px-4 py-3 flex justify-between items-center">
                            <p class="text-2xl font-bold text-gray-800">{{ $this->professionals->total() }}</p>
                            <span class="text-sm text-gray-500">{{ __('public.technical_official_registry.officials') }}</span>
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
                            {{ __('public.technical_official_registry.clear_filters') }}
                        </button>
                    </div>

                    <div class="space-y-4" x-data="{ nameOpen: true, districtOpen: false, sportOpen: false, statusOpen: false }">
                        {{-- Name Filter --}}
                        <div class="bg-white rounded-xl overflow-hidden shadow-sm border border-gray-100">
                            <button type="button" @click="nameOpen = !nameOpen"
                                class="w-full px-4 py-3 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-blue-100 flex justify-between items-center text-left">
                                <p class="text-xs font-medium text-blue-800 uppercase tracking-wider">
                                    {{ __('public.technical_official_registry.filters.name') }}
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
                                        placeholder="{{ __('public.technical_official_registry.filters.name_placeholder') }}"
                                        class="pl-10 block w-full rounded-lg border-gray-300 bg-white text-gray-900 placeholder-gray-500 focus:ring-blue-500 focus:border-blue-500 shadow-sm py-2.5 text-sm">
                                </div>
                            </div>
                        </div>

                        {{-- District Filter --}}
                        @if ($this->districts->isNotEmpty())
                            <div class="bg-white rounded-xl overflow-hidden shadow-sm border border-gray-100">
                                <button type="button" @click="districtOpen = !districtOpen"
                                    class="w-full px-4 py-3 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-blue-100 flex justify-between items-center text-left">
                                    <p class="text-xs font-medium text-blue-800 uppercase tracking-wider">
                                        {{ __('public.technical_official_registry.filters.district') }}
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
                                        <option value="">{{ __('public.technical_official_registry.filters.all_districts') }}</option>
                                        @foreach ($this->districts as $district)
                                            <option value="{{ $district->id }}">{{ $district->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif

                        {{-- Sport Filter --}}
                        @if ($this->sports->isNotEmpty())
                            <div class="bg-white rounded-xl overflow-hidden shadow-sm border border-gray-100">
                                <button type="button" @click="sportOpen = !sportOpen"
                                    class="w-full px-4 py-3 bg-gradient-to-r from-blue-50 to-blue-100 border-b border-blue-100 flex justify-between items-center text-left">
                                    <p class="text-xs font-medium text-blue-800 uppercase tracking-wider">
                                        {{ __('public.technical_official_registry.filters.sport') }}
                                    </p>
                                    <svg class="w-4 h-4 text-blue-600 transform transition-transform duration-200"
                                        :class="{ 'rotate-180': sportOpen }" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div x-show="sportOpen" x-collapse class="p-3">
                                    <select wire:model.live="selectedSport"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                        <option value="">{{ __('public.technical_official_registry.filters.all_sports') }}</option>
                                        @foreach ($this->sports as $sport)
                                            <option value="{{ $sport->id }}">{{ $sport->translated_name }}</option>
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
                                    {{ __('public.technical_official_registry.filters.status') }}
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
                                    <option value="">{{ __('public.technical_official_registry.filters.all_statuses') }}</option>
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
                        {{ __('public.technical_official_registry.legend.title') }}
                    </h3>

                    {{-- RNOT Description --}}
                    <div class="mb-4 p-4 bg-blue-50 rounded-xl border border-blue-100">
                        <p class="text-xs text-gray-600 leading-relaxed mb-2">
                            {{ __('public.technical_official_registry.subtitle_paragraph_1') }}
                        </p>
                        <p class="text-xs text-gray-600 leading-relaxed mb-2">
                            {{ __('public.technical_official_registry.subtitle_paragraph_2') }}
                        </p>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            {{ __('public.technical_official_registry.subtitle_paragraph_3') }}
                        </p>
                    </div>

                    <hr class="my-4 border-gray-200">

                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                        {{ __('public.technical_official_registry.legend.subtitle') }}
                    </h4>

                    <div class="space-y-3">
                        {{-- Active --}}
                        <div class="flex items-start space-x-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 flex-shrink-0 mt-0.5">
                                {{ __('public.technical_official_registry.status.active') }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            {{ __('public.technical_official_registry.legend.active_description') }}
                        </p>

                        {{-- Expired --}}
                        <div class="flex items-start space-x-2 mt-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 flex-shrink-0 mt-0.5">
                                {{ __('public.technical_official_registry.status.expired') }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            {{ __('public.technical_official_registry.legend.expired_description') }}
                        </p>

                        {{-- Suspended --}}
                        <div class="flex items-start space-x-2 mt-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 flex-shrink-0 mt-0.5">
                                {{ __('public.technical_official_registry.status.suspended') }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            {{ __('public.technical_official_registry.legend.suspended_description') }}
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
                        <h1 class="text-lg font-bold text-gray-900">{{ __('public.technical_official_registry.title') }}</h1>
                        <p class="text-sm text-gray-500">{{ $this->professionals->total() }} {{ __('public.technical_official_registry.officials') }}</p>
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

            {{-- Officials Grid --}}
            <div class="p-4 md:p-6">
                {{-- Loading Indicator --}}
                <div wire:loading.delay wire:target="searchName, selectedDistrict, selectedSport, selectedStatus"
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

                @if ($this->professionals->count() > 0)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('public.technical_official_registry.table.name') }}
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('public.technical_official_registry.table.district') }}
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('public.technical_official_registry.table.certification') }}
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('public.technical_official_registry.table.certification_status') }}
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('public.technical_official_registry.table.license_status') }}
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('public.technical_official_registry.table.view_profile') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($this->professionals as $professional)
                                        @foreach ($professional->certificationsAttributed as $certIndex => $certAttr)
                                            @php
                                                $status = $this->getCertificationStatus($certAttr);
                                                $certName = $certAttr->certification?->name;
                                                $certLicenseId = $certAttr->certification?->license_id;
                                                $matchingLicense = $certLicenseId
                                                    ? $professional->licenses->first(fn ($l) => $l->license_id === $certLicenseId)
                                                    : null;
                                                $licenseStatus = $matchingLicense ? $this->getLicenseStatus($matchingLicense) : null;
                                                $isFirst = $certIndex === 0;
                                                $certCount = $professional->certificationsAttributed->count();
                                            @endphp
                                            <tr wire:key="professional-{{ $professional->id }}-cert-{{ $certAttr->id }}"
                                                class="hover:bg-gray-50 transition-colors duration-150 {{ !$isFirst ? 'border-t border-gray-100' : '' }}">
                                                @if ($isFirst)
                                                    {{-- Name with Avatar --}}
                                                    <td class="px-4 py-3 whitespace-nowrap" @if($certCount > 1) rowspan="{{ $certCount }}" @endif>
                                                        <div class="flex items-center gap-3">
                                                            <div class="h-10 w-10 rounded-full overflow-hidden bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center flex-shrink-0"
                                                                x-data="{ imageError: false }">
                                                                @if ($professional->hasProfileImage())
                                                                    <img src="{{ $professional->avatar_url }}"
                                                                        alt="{{ $professional->name }} {{ $professional->surname }}"
                                                                        class="h-full w-full object-cover"
                                                                        x-show="!imageError"
                                                                        x-on:error="imageError = true">
                                                                    <span x-show="imageError" x-cloak class="text-sm font-bold text-blue-600">
                                                                        {{ mb_substr($professional->name, 0, 1) }}{{ mb_substr($professional->surname, 0, 1) }}
                                                                    </span>
                                                                @else
                                                                    <span class="text-sm font-bold text-blue-600">
                                                                        {{ mb_substr($professional->name, 0, 1) }}{{ mb_substr($professional->surname, 0, 1) }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                            <span class="text-sm font-medium text-gray-900">
                                                                {{ $professional->name }} {{ $professional->surname }}
                                                            </span>
                                                        </div>
                                                    </td>

                                                    {{-- District --}}
                                                    <td class="px-4 py-3 whitespace-nowrap" @if($certCount > 1) rowspan="{{ $certCount }}" @endif>
                                                        <span class="text-sm text-gray-600">
                                                            {{ $professional->district?->name ?? '-' }}
                                                        </span>
                                                    </td>
                                                @endif

                                                {{-- Certification Name --}}
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <span class="text-sm text-gray-900">
                                                        {{ $certName }}
                                                    </span>
                                                </td>

                                                {{-- Certification Status --}}
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                        {{ $status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                                        {{ $status === 'expired' ? 'bg-amber-100 text-amber-800' : '' }}
                                                        {{ $status === 'suspended' ? 'bg-red-100 text-red-800' : '' }}">
                                                        {{ __('public.technical_official_registry.certification_status_values.' . $status) }}
                                                    </span>
                                                </td>

                                                {{-- License Status --}}
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    @if ($licenseStatus)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                            {{ $licenseStatus === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                                            {{ $licenseStatus === 'expired' ? 'bg-amber-100 text-amber-800' : '' }}
                                                            {{ $licenseStatus === 'suspended' ? 'bg-red-100 text-red-800' : '' }}">
                                                            {{ __('public.technical_official_registry.status.' . $licenseStatus) }}
                                                        </span>
                                                    @else
                                                        <span class="text-sm text-gray-400">-</span>
                                                    @endif
                                                </td>

                                                @if ($isFirst)
                                                    {{-- View Profile --}}
                                                    <td class="px-4 py-3 whitespace-nowrap text-right" @if($certCount > 1) rowspan="{{ $certCount }}" @endif>
                                                        <a href="{{ route('public.technical-official-profile', $professional) }}"
                                                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors duration-150">
                                                            {{ __('public.technical_official_registry.table.view_profile') }}
                                                        </a>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-6">
                        {{ $this->professionals->links() }}
                    </div>
                @else
                    {{-- Empty State --}}
                    <div class="text-center py-12">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-1">
                            {{ __('public.technical_official_registry.no_results') }}
                        </h3>
                        <p class="text-sm text-gray-500 max-w-md mx-auto mb-4">
                            {{ __('public.technical_official_registry.no_results_hint') }}
                        </p>
                        <button wire:click="clearFilters"
                            class="inline-flex items-center px-4 py-2 border border-blue-300 text-sm font-medium rounded-lg text-blue-700 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            {{ __('public.technical_official_registry.clear_filters') }}
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
