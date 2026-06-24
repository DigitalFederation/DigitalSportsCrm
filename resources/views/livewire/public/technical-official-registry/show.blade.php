<div class="min-h-screen bg-gray-50">

    {{-- Accent Bar --}}
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 h-32"></div>

    {{-- Profile Card --}}
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 -mt-16 pb-0">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            {{-- Back Link --}}
            <div class="mb-4">
                <a href="{{ route('public.technical-official-registry') }}"
                    class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    {{ __('public.technical_official_registry.profile.back') }}
                </a>
            </div>

            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5">
                {{-- Avatar --}}
                <div class="h-24 w-24 rounded-full overflow-hidden bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center flex-shrink-0 ring-4 ring-blue-50 shadow-sm"
                    x-data="{ imageError: false }">
                    @if ($individual->hasProfileImage())
                        <img src="{{ $individual->avatar_url }}"
                            alt="{{ $individual->name }} {{ $individual->surname }}"
                            class="h-full w-full object-cover"
                            x-show="!imageError"
                            x-on:error="imageError = true">
                        <span x-show="imageError" x-cloak class="text-3xl font-bold text-blue-600">
                            {{ mb_substr($individual->name, 0, 1) }}{{ mb_substr($individual->surname, 0, 1) }}
                        </span>
                    @else
                        <span class="text-3xl font-bold text-blue-600">
                            {{ mb_substr($individual->name, 0, 1) }}{{ mb_substr($individual->surname, 0, 1) }}
                        </span>
                    @endif
                </div>

                {{-- Info --}}
                <div class="text-center sm:text-left flex-1">
                    <h1 class="text-2xl font-bold text-gray-900">
                        {{ $individual->name }} {{ $individual->surname }}
                    </h1>

                    <div class="mt-2 space-y-1">
                        @if ($individual->district)
                            <p class="text-sm text-gray-500 flex items-center justify-center sm:justify-start gap-1.5">
                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                {{ $individual->district->name }}
                            </p>
                        @endif

                        @if ($individual->gender)
                            <p class="text-sm text-gray-500 flex items-center justify-center sm:justify-start gap-1.5">
                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                {{ __('individuals.' . $individual->gender) }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-12 space-y-8">

        {{-- Certifications Section --}}
        @if ($this->certifications->isNotEmpty())
            <div>
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="inline-block w-2 h-2 rounded-full bg-blue-600"></span>
                    {{ __('public.technical_official_registry.profile.certifications') }}
                </h2>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-2/5">
                                        {{ __('public.technical_official_registry.profile.table.certification') }}
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('public.technical_official_registry.profile.table.modality') }}
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('public.technical_official_registry.profile.table.certification_status') }}
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('public.technical_official_registry.profile.table.license_status') }}
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('public.technical_official_registry.profile.table.experience_points') }}
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('public.technical_official_registry.profile.table.level') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($this->certifications as $cert)
                                    @php
                                        $status = $this->getCertificationStatus($cert);
                                        $certLicenseId = $cert->certification?->license_id;
                                        $matchingLicense = $certLicenseId
                                            ? $individual->licenses->first(fn ($l) => $l->license_id === $certLicenseId)
                                            : null;
                                        $licenseStatus = $matchingLicense ? $this->getLicenseStatus($matchingLicense) : null;
                                        $sportId = $cert->certification?->license?->sport_id;
                                        $summary = $sportId ? ($this->sportSummaries[$sportId] ?? null) : null;
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            {{ $cert->certification_name }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                            {{ $cert->certification?->license?->sport?->translated_name ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ $status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $status === 'expired' ? 'bg-amber-100 text-amber-800' : '' }}
                                                {{ $status === 'suspended' ? 'bg-red-100 text-red-800' : '' }}">
                                                {{ __('public.technical_official_registry.status.' . $status) }}
                                            </span>
                                        </td>
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
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 tabular-nums">
                                            {{ $summary?->total_experience_points ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            @if ($summary?->average_evaluation)
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-sm font-semibold text-gray-900 tabular-nums">{{ $summary->average_evaluation }}</span>
                                                    <div class="flex items-center gap-0.5">
                                                        @for ($i = 1; $i <= 5; $i++)
                                                            @if ($i <= round($summary->average_evaluation))
                                                                <x-heroicon-s-star class="w-3.5 h-3.5 text-amber-400" />
                                                            @else
                                                                <x-heroicon-o-star class="w-3.5 h-3.5 text-gray-300" />
                                                            @endif
                                                        @endfor
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-sm text-gray-400">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- Competitions Section --}}
        <div>
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                <span class="inline-block w-2 h-2 rounded-full bg-blue-600"></span>
                {{ __('public.technical_official_registry.profile.competitions') }}
            </h2>

            @if ($this->competitions->isNotEmpty())
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('public.technical_official_registry.profile.sport') }}
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('public.technical_official_registry.profile.start_date') }}
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('public.technical_official_registry.profile.end_date') }}
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('public.technical_official_registry.profile.event') }}
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('public.technical_official_registry.profile.entity') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($this->competitions as $enrollment)
                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                            {{ $enrollment->event?->sport?->translated_name ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                            {{ $enrollment->event?->start_date?->format('d/m/Y') ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                            {{ $enrollment->event?->end_date?->format('d/m/Y') ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            {{ $enrollment->event?->name ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                            {{ $enrollment->entity?->name ?? '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
                    <div class="flex justify-center mb-3">
                        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500">{{ __('public.technical_official_registry.profile.no_competitions') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
