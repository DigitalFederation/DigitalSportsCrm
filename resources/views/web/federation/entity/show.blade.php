@push('head-css')
    @if($entity->lat && $entity->lng)
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <style>
            .leaflet-popup-content-wrapper {
                border-radius: 8px;
            }
            .leaflet-popup-content {
                margin: 12px 16px;
            }
        </style>
    @endif
@endpush

@push('footer-scripts')
    @if($entity->lat && $entity->lng)
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    @endif
@endpush

<x-layout>
    <div class="previous-layout-classes">
        <!-- Page Header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-6">
            <div class="mb-4 sm:mb-0">
                <div class="flex items-center gap-4">
                    @if($entity->getFirstMediaUrl('profile', 'thumb'))
                        <img
                            class="h-16 w-16 object-cover rounded-xl border-2 border-slate-200 dark:border-slate-700 shadow-sm"
                            src="{{ $entity->getFirstMediaUrl('profile', 'thumb') }}"
                            alt="{{ $entity->name }}"
                        >
                    @else
                        <div class="h-16 w-16 rounded-xl bg-gradient-to-br from-slate-600 to-slate-800 dark:from-slate-500 dark:to-slate-700 flex items-center justify-center shadow-sm border-2 border-slate-200 dark:border-slate-700">
                            <span class="text-white font-bold text-xl">
                                {{ strtoupper(substr($entity->name, 0, 2)) }}
                            </span>
                        </div>
                    @endif
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">{{ $entity->name }}</h1>
                        <div class="flex items-center gap-2 mt-1">
                            @if($entity->country)
                                <img
                                    src="{{ asset('img/flags/' . strtolower($entity->country->iso) . '.svg') }}"
                                    alt="{{ $entity->country->name }}"
                                    class="w-5 h-4 rounded shadow-sm"
                                >
                                <span class="text-sm text-slate-500 dark:text-slate-400">{{ $entity->country->name }}</span>
                            @endif
                            @if($entity->member_code)
                                <span class="text-slate-300 dark:text-slate-600">|</span>
                                <span class="text-sm font-mono text-slate-500 dark:text-slate-400">{{ $entity->member_code }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a href="{{ route('federation.entity.index') }}" class="btn btn-secondary">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    {{ __('main.Back') }}
                </a>
            </div>
        </div>

        <!-- Two Column Layout: Info & Contact -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Entity Information -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm p-6">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-5 flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    {{ __('entity.information') }}
                </h3>
                <dl class="space-y-4">
                    <!-- Entity Name -->
                    <div>
                        <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ __('entity.name') }}</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $entity->name }}</dd>
                    </div>

                    <!-- Member Code -->
                    @if($entity->member_code)
                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ __('main.Member Code') }}</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-mono font-medium bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                    {{ $entity->member_code }}
                                </span>
                            </dd>
                        </div>
                    @endif

                    <!-- Tax ID -->
                    @if($entity->vat_number)
                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ __('entity.tax_identification_number') }}</dt>
                            <dd class="mt-1 text-sm text-slate-700 dark:text-slate-300">{{ $entity->vat_number }}</dd>
                        </div>
                    @endif

                    <!-- Address -->
                    <div>
                        <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ __('entity.address') }}</dt>
                        <dd class="mt-1">
                            @if($entity->address || $entity->location || $entity->zip_code)
                                <address class="not-italic text-sm text-slate-700 dark:text-slate-300 leading-relaxed">
                                    @if($entity->address)
                                        <span class="block">{{ $entity->address }}</span>
                                    @endif
                                    @if($entity->zip_code || $entity->location)
                                        <span class="block">
                                            @if($entity->zip_code){{ $entity->zip_code }}@endif
                                            @if($entity->zip_code && $entity->location), @endif
                                            @if($entity->location){{ $entity->location }}@endif
                                        </span>
                                    @endif
                                    @if($entity->country)
                                        <span class="block text-slate-500 dark:text-slate-400">{{ $entity->country->name }}</span>
                                    @endif
                                </address>
                            @else
                                <span class="text-sm text-slate-400 dark:text-slate-500 italic">{{ __('main.Not available') }}</span>
                            @endif
                        </dd>
                    </div>
                </dl>

                <!-- Individuals Count -->
                <div class="mt-6 pt-6 border-t border-slate-200 dark:border-slate-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-3xl font-bold text-slate-900 dark:text-white">{{ $entity->individuals->count() }}</div>
                            <div class="text-sm text-slate-500 dark:text-slate-400">{{ __('entity.individuals') }}</div>
                        </div>
                        <a href="{{ route('federation.individual.index', ['filter[filter_entity]' => $entity->id]) }}" class="btn btn-outline btn-sm">
                            {{ __('entity.view_all') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm p-6">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide mb-5 flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    {{ __('main.Contact Information') }}
                </h3>
                <div class="space-y-4">
                    <!-- Email -->
                    <div class="flex items-start gap-3 p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg">
                        <div class="flex-shrink-0 w-10 h-10 bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ __('entity.contact_email') }}</dt>
                            @if($entity->email)
                                <dd class="mt-1">
                                    <a href="mailto:{{ $entity->email }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium truncate block">
                                        {{ $entity->email }}
                                    </a>
                                </dd>
                            @else
                                <dd class="mt-1 text-sm text-slate-400 dark:text-slate-500 italic">{{ __('main.Not available') }}</dd>
                            @endif
                        </div>
                    </div>

                    <!-- Phone -->
                    <div class="flex items-start gap-3 p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg">
                        <div class="flex-shrink-0 w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ __('entity.phone_number') }}</dt>
                            @if($entity->phone)
                                <dd class="mt-1">
                                    <a href="tel:{{ $entity->phone }}" class="text-sm text-slate-800 dark:text-slate-200 font-medium hover:text-emerald-600 dark:hover:text-emerald-400">
                                        {{ $entity->phone }}
                                    </a>
                                </dd>
                            @else
                                <dd class="mt-1 text-sm text-slate-400 dark:text-slate-500 italic">{{ __('main.Not available') }}</dd>
                            @endif
                        </div>
                    </div>

                    <!-- Website -->
                    <div class="flex items-start gap-3 p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg">
                        <div class="flex-shrink-0 w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ __('entity.website') }}</dt>
                            @if($entity->website)
                                <dd class="mt-1">
                                    <a href="{{ $entity->website }}" target="_blank" rel="noopener noreferrer" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium inline-flex items-center gap-1">
                                        {{ $entity->website }}
                                        <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </a>
                                </dd>
                            @else
                                <dd class="mt-1 text-sm text-slate-400 dark:text-slate-500 italic">{{ __('main.Not available') }}</dd>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mt-6">
            <!-- Diving Certifications -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ $certificationsCount['DIVING'] ?? 0 }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('entity.diving_certifications') }}</div>
                <a href="{{ route('federation.certification-attributed.index', ['filter[committee]' => 'diving', 'filter[filter_entity]' => $entity->id]) }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline mt-2 inline-block">{{ __('entity.view_all') }}</a>
            </div>

            <!-- Scientific Certifications -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ $certificationsCount['SCIENTIFIC'] ?? 0 }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('entity.scientific_certifications') }}</div>
                <a href="{{ route('federation.certification-attributed.index', ['filter[committee]' => 'scientific', 'filter[filter_entity]' => $entity->id]) }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline mt-2 inline-block">{{ __('entity.view_all') }}</a>
            </div>

            <!-- Diving Licenses -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ $licensesCount['DIVING'] ?? 0 }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('entity.diving_licenses') }}</div>
                <a href="{{ route('federation.license-attributed.index', ['filter[committee]' => 'diving', 'filter[filter_entity]' => $entity->id]) }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline mt-2 inline-block">{{ __('entity.view_all') }}</a>
            </div>

            <!-- Scientific Licenses -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ $licensesCount['SCIENTIFIC'] ?? 0 }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('entity.scientific_licenses') }}</div>
                <a href="{{ route('federation.license-attributed.index', ['filter[committee]' => 'scientific', 'filter[filter_entity]' => $entity->id]) }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline mt-2 inline-block">{{ __('entity.view_all') }}</a>
            </div>

            <!-- Sport Licenses -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-slate-900 dark:text-white">{{ $licensesCount['SPORT'] ?? 0 }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('entity.sport_licenses') }}</div>
                <a href="{{ route('federation.license-attributed.index', ['filter[committee]' => 'sport', 'filter[filter_entity]' => $entity->id]) }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline mt-2 inline-block">{{ __('entity.view_all') }}</a>
            </div>
        </div>

        <!-- Location Map -->
        @if($entity->lat && $entity->lng)
            <div class="mt-6 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        {{ __('entity.hq_location') }}
                    </h3>
                </div>
                <div
                    x-data="{ map: null }"
                    x-init="$nextTick(() => {
                        map = L.map($refs.mapContainer).setView([{{ $entity->lat }}, {{ $entity->lng }}], 15);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OpenStreetMap'
                        }).addTo(map);
                        const marker = L.marker([{{ $entity->lat }}, {{ $entity->lng }}]).addTo(map);
                        marker.bindPopup('<div class=\'text-center\'><strong>{{ addslashes($entity->name) }}</strong>@if($entity->address)<br><span class=\'text-slate-600 text-xs\'>{{ addslashes($entity->address) }}</span>@endif</div>').openPopup();
                        setTimeout(() => map.invalidateSize(), 100);
                    })"
                    class="relative"
                >
                    <div x-ref="mapContainer" class="h-64 sm:h-80 w-full z-0"></div>

                    <!-- Directions Link -->
                    <a
                        href="https://www.openstreetmap.org/directions?from=&to={{ $entity->lat }}%2C{{ $entity->lng }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="absolute bottom-4 right-4 z-[1000] inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg shadow-lg text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                        {{ __('entity.get_directions') }}
                    </a>
                </div>
            </div>
        @endif

        <!-- Instructors Table -->
        <div class="mt-6 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    {{ __('entity.instructors') }}
                </h3>
                <span class="text-sm text-slate-500 dark:text-slate-400">{{ count($entity->entityProfessionals) }} {{ __('entity.active') }}</span>
            </div>
            @if($entity->entityProfessionals->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800/50">
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    {{ __('entity.table_name') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    {{ __('main.Member Code') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @foreach($entity->entityProfessionals as $instructor)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-medium text-slate-900 dark:text-white">{{ $instructor->instructor?->name }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <span class="text-sm font-mono text-slate-500 dark:text-slate-400">{{ $instructor->instructor?->member_code }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">
                    <a href="{{ route('federation.individual.index', ['filter[filter_instructors]' => true, 'filter[filter_entity]' => $entity->id]) }}" class="btn btn-outline btn-sm">
                        {{ __('entity.see_all_instructors') }}
                    </a>
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ __('entity.no_instructors_yet') }}</p>
                </div>
            @endif
        </div>

        <!-- Federation Affiliations -->
        <div class="mt-6 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wide flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    {{ __('entity.federation_and_associations') }}
                </h3>
            </div>
            @if($entity->federations->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800/50">
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    {{ __('entity.table_federation') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    {{ __('entity.table_type') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    {{ __('entity.table_status') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    {{ __('entity.table_national_number') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @foreach($entity->federations as $federation)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            @if($federation->getFirstMediaUrl('logo'))
                                                <img class="h-8 w-8 rounded-full object-cover" src="{{ $federation->getFirstMediaUrl('logo') }}" alt="{{ $federation->name }}">
                                            @else
                                                <div class="h-8 w-8 rounded-full bg-slate-200 dark:bg-slate-600 flex items-center justify-center">
                                                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400">{{ strtoupper(substr($federation->name, 0, 2)) }}</span>
                                                </div>
                                            @endif
                                            <span class="text-sm font-medium text-slate-900 dark:text-white">{{ $federation->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($federation->parent_id)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300">
                                                {{ __('entity.local_federation') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-300">
                                                {{ __('entity.main_federation') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <x-ux-badge-component
                                            :status="$entity->getFederationStateNameAttribute($federation)"
                                            :color="$entity->getFederationStateColorAttribute($federation)"
                                        />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-mono text-slate-600 dark:text-slate-300">{{ $federation->pivot->national_federation_number ?: '-' }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">{{ __('entity.no_federation_memberships') }}</p>
                </div>
            @endif
        </div>
    </div>
</x-layout>
