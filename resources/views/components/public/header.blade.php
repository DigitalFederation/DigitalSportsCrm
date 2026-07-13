{{-- Shared public header navigation --}}
@props(['currentPage' => null])

@php
    $brand = config('branding.primary');
    $underwaterSportsPages = ['club-registry', 'coach-registry', 'technical-official-registry'];
    $divingPages = ['diving-service-providers', 'diving-professionals'];
@endphp

<header class="bg-white shadow-md border-b border-slate-200 sticky top-0 z-40" x-data="{ mobileMenu: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Project logo -->
            <div class="flex items-center space-x-4">
                <a href="{{ url('/') }}" class="flex items-center space-x-3">
                    <x-brand-logo class="h-10 w-auto" text-class="text-slate-800 font-bold text-lg" />
                    <div class="hidden sm:block">
                        <div class="text-slate-800 font-bold text-base">{{ $brand['short_name'] }}</div>
                    </div>
                </a>
            </div>

            <!-- Desktop navigation -->
            <div class="hidden lg:flex items-center space-x-8">
                <nav class="flex items-center space-x-6">
                    {{-- 1. Inicio - Only show when not on home page --}}
                    @if ($currentPage !== 'home')
                        <a href="{{ url('/') }}"
                            class="text-slate-700 hover:text-blue-600 transition-colors duration-200 font-medium">
                            {{ __('welcome.home') }}
                        </a>
                    @endif

                    {{-- 2. Mapa de Entidades --}}
                    <a href="{{ route('public.map.locations') }}"
                        class="text-slate-700 hover:text-blue-600 transition-colors duration-200 font-medium {{ $currentPage === 'map' ? 'text-blue-600' : '' }}">
                        {{ __('welcome.community_map') }}
                    </a>

                    {{-- 3. Eventos --}}
                    <a href="{{ route('public.events') }}"
                        class="text-slate-700 hover:text-blue-600 transition-colors duration-200 font-medium {{ $currentPage === 'events' ? 'text-blue-600' : '' }}">
                        {{ __('welcome.events') }}
                    </a>

                    {{-- 4. Desporto Subaquatico (Dropdown) --}}
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button type="button"
                            class="flex items-center text-slate-700 hover:text-blue-600 transition-colors duration-200 font-medium {{ in_array($currentPage, $underwaterSportsPages) ? 'text-blue-600' : '' }}"
                            @click="open = !open">
                            {{ __('welcome.underwater_sports') }}
                            <svg class="ml-1 h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        {{-- Dropdown menu --}}
                        <div x-show="open"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute left-0 mt-2 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 py-1 z-50 whitespace-nowrap"
                            x-cloak>
                            <a href="{{ route('public.club-registry') }}"
                                class="block px-4 py-2 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-600 whitespace-nowrap {{ $currentPage === 'club-registry' ? 'bg-blue-50 text-blue-600' : '' }}">
                                {{ __('welcome.club_registry') }}
                            </a>
                            <a href="{{ route('public.coach-registry') }}"
                                class="block px-4 py-2 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-600 whitespace-nowrap {{ $currentPage === 'coach-registry' ? 'bg-blue-50 text-blue-600' : '' }}">
                                {{ __('welcome.coach_registry') }}
                            </a>
                            <a href="{{ route('public.technical-official-registry') }}"
                                class="block px-4 py-2 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-600 whitespace-nowrap {{ $currentPage === 'technical-official-registry' ? 'bg-blue-50 text-blue-600' : '' }}">
                                {{ __('welcome.technical_official_registry') }}
                            </a>
                        </div>
                    </div>

                    {{-- 4. Mergulho Recreativo e Cientifico (Dropdown) --}}
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button type="button"
                            class="flex items-center text-slate-700 hover:text-blue-600 transition-colors duration-200 font-medium {{ in_array($currentPage, $divingPages) ? 'text-blue-600' : '' }}"
                            @click="open = !open">
                            {{ __('welcome.recreational_scientific_diving') }}
                            <svg class="ml-1 h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        {{-- Dropdown menu --}}
                        <div x-show="open"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute left-0 mt-2 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 py-1 z-50 whitespace-nowrap"
                            x-cloak>
                            <a href="{{ route('public.diving-service-providers') }}"
                                class="block px-4 py-2 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-600 whitespace-nowrap {{ $currentPage === 'diving-service-providers' ? 'bg-blue-50 text-blue-600' : '' }}">
                                {{ __('welcome.diving_service_providers') }}
                            </a>
                            <a href="{{ route('public.diving-professionals') }}"
                                class="block px-4 py-2 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-600 whitespace-nowrap {{ $currentPage === 'diving-professionals' ? 'bg-blue-50 text-blue-600' : '' }}">
                                {{ __('welcome.diving_professionals') }}
                            </a>
                        </div>
                    </div>
                </nav>

                <!-- Auth button -->
                <a href="{{ route('login') }}"
                    class="bg-blue-600 hover:bg-blue-700 !text-white px-5 py-2 rounded-lg transition-colors duration-200 font-medium text-sm">
                    {{ __('welcome.sign_in') }}
                </a>
            </div>

            <!-- Mobile hamburger -->
            <div class="lg:hidden">
                <button @click="mobileMenu = !mobileMenu"
                        class="text-slate-700 p-2 rounded-lg hover:bg-slate-100 transition-colors duration-200">
                    <svg x-show="!mobileMenu" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg x-show="mobileMenu" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile menu -->
    <div x-show="mobileMenu" x-cloak x-collapse class="lg:hidden border-t border-slate-200 bg-white">
        <div class="px-4 py-4 space-y-1">
            {{-- 1. Inicio - Only show when not on home page --}}
            @if ($currentPage !== 'home')
                <a href="{{ url('/') }}"
                    class="block px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 font-medium">
                    {{ __('welcome.home') }}
                </a>
            @endif

            {{-- 2. Mapa de Entidades --}}
            <a href="{{ route('public.map.locations') }}"
                class="block px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 font-medium {{ $currentPage === 'map' ? 'bg-blue-50 text-blue-600' : '' }}">
                {{ __('welcome.community_map') }}
            </a>

            {{-- 3. Eventos --}}
            <a href="{{ route('public.events') }}"
                class="block px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 font-medium {{ $currentPage === 'events' ? 'bg-blue-50 text-blue-600' : '' }}">
                {{ __('welcome.events') }}
            </a>

            {{-- 4. Desporto Subaquatico (Collapsible) --}}
            <div x-data="{ mobileSubmenu: {{ in_array($currentPage, $underwaterSportsPages) ? 'true' : 'false' }} }">
                <button @click="mobileSubmenu = !mobileSubmenu"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 font-medium {{ in_array($currentPage, $underwaterSportsPages) ? 'text-blue-600' : '' }}">
                    <span>{{ __('welcome.underwater_sports') }}</span>
                    <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': mobileSubmenu }"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="mobileSubmenu" x-collapse class="pl-4 mt-1 space-y-1">
                    <a href="{{ route('public.club-registry') }}"
                        class="block px-3 py-2 rounded-lg text-slate-600 hover:bg-slate-100 text-sm {{ $currentPage === 'club-registry' ? 'bg-blue-50 text-blue-600' : '' }}">
                        {{ __('welcome.club_registry') }}
                    </a>
                    <a href="{{ route('public.coach-registry') }}"
                        class="block px-3 py-2 rounded-lg text-slate-600 hover:bg-slate-100 text-sm {{ $currentPage === 'coach-registry' ? 'bg-blue-50 text-blue-600' : '' }}">
                        {{ __('welcome.coach_registry') }}
                    </a>
                    <a href="{{ route('public.technical-official-registry') }}"
                        class="block px-3 py-2 rounded-lg text-slate-600 hover:bg-slate-100 text-sm {{ $currentPage === 'technical-official-registry' ? 'bg-blue-50 text-blue-600' : '' }}">
                        {{ __('welcome.technical_official_registry') }}
                    </a>
                </div>
            </div>

            {{-- 4. Mergulho Recreativo e Cientifico (Collapsible) --}}
            <div x-data="{ mobileSubmenu: {{ in_array($currentPage, $divingPages) ? 'true' : 'false' }} }">
                <button @click="mobileSubmenu = !mobileSubmenu"
                    class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-slate-700 hover:bg-slate-100 font-medium {{ in_array($currentPage, $divingPages) ? 'text-blue-600' : '' }}">
                    <span>{{ __('welcome.recreational_scientific_diving') }}</span>
                    <svg class="h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': mobileSubmenu }"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div x-show="mobileSubmenu" x-collapse class="pl-4 mt-1 space-y-1">
                    <a href="{{ route('public.diving-service-providers') }}"
                        class="block px-3 py-2 rounded-lg text-slate-600 hover:bg-slate-100 text-sm {{ $currentPage === 'diving-service-providers' ? 'bg-blue-50 text-blue-600' : '' }}">
                        {{ __('welcome.diving_service_providers') }}
                    </a>
                    <a href="{{ route('public.diving-professionals') }}"
                        class="block px-3 py-2 rounded-lg text-slate-600 hover:bg-slate-100 text-sm {{ $currentPage === 'diving-professionals' ? 'bg-blue-50 text-blue-600' : '' }}">
                        {{ __('welcome.diving_professionals') }}
                    </a>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-200">
                <a href="{{ route('login') }}"
                    class="block w-full text-center bg-blue-600 hover:bg-blue-700 !text-white px-4 py-2.5 rounded-lg font-medium">
                    {{ __('welcome.sign_in') }}
                </a>
            </div>
        </div>
    </div>
</header>
