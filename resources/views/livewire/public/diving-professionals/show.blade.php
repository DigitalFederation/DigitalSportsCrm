<div class="min-h-screen bg-gray-50">

    {{-- Accent Bar --}}
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 h-32"></div>

    {{-- Profile Card --}}
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 -mt-16 pb-0">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            {{-- Back Link --}}
            <div class="mb-4">
                <a href="{{ route('public.diving-professionals') }}"
                    class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    {{ __('public.diving_professionals.profile.back') }}
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

        {{-- Licenses Section --}}
        @if ($individual->licenses->isNotEmpty())
            <div>
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="inline-block w-2 h-2 rounded-full bg-blue-600"></span>
                    {{ __('public.diving_professionals.profile.licenses') }}
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
                    @foreach ($individual->licenses as $license)
                        @php
                            $status = $this->getLicenseStatusForAttributed($license);
                            $licenseName = $license->license?->sport?->translated_name ?? $license->license?->name;
                        @endphp
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow duration-150">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $licenseName }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $license->license?->name }}</p>
                                </div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium whitespace-nowrap
                                    {{ $status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $status === 'expired' ? 'bg-amber-100 text-amber-800' : '' }}
                                    {{ $status === 'suspended' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ __('public.diving_professionals.status.' . $status) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
