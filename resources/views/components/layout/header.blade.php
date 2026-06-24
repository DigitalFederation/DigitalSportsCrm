<header class="sticky top-0 z-30 rounded-tl-2xl">
    <!-- Main header with glass effect -->
    <div class="bg-white/95 backdrop-blur-md shadow-md">
        <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Header: Left side -->
                <div class="flex items-center space-x-4">
                    <!-- Hamburger button with improved styling -->
                    <button
                        class="text-primary hover:text-primary-light bg-primary/10 hover:bg-primary/20 p-2 rounded-lg transition-all duration-200 lg:hidden flex items-center justify-center"
                        @click.stop="sidebarOpen = !sidebarOpen"
                        aria-controls="sidebar"
                        :aria-expanded="sidebarOpen"
                    >
                        <span class="sr-only">Open sidebar</span>
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <rect x="4" y="5" width="16" height="2"/>
                            <rect x="4" y="11" width="16" height="2"/>
                            <rect x="4" y="17" width="16" height="2"/>
                        </svg>
                    </button>
                    
                    <!-- Company logo/branding -->
                    <div class="hidden lg:flex items-center">
                        <div class="flex items-center space-x-1">
                            <span class="text-lg font-bold bg-clip-text text-slate-600">
                                @if(Auth::check() && Auth::user()->group()->first()->code == 'ENTITY')
                                    {{ Auth::user()->getEntity() ? Auth::user()->getEntity()->name : 'Portal Subaquático' }}
                                @elseif(Auth::check() && Auth::user()->group()->first()->code == 'FEDERATION')
                                    {{ Auth::user()->getFederation() ? Auth::user()->getFederation()->name : 'Portal Subaquático' }}
                                @else
                                    Portal Subaquático
                                @endif
                            </span>
                        </div>
                    </div>
                    
                    @php
                        $segment = Request::segment(1);
                        $title = match($segment) {
                            'admin' => 'Administrador',
                            'federation' => '',
                            'entity' => 'Entidade',
                            'individual' => '',
                            default => ucfirst($segment)
                        };
                    @endphp

                    @if($title)
                        <!-- Vertical divider -->
                        <div class="hidden lg:block h-8 w-px bg-gray-200 mx-2"></div>

                        <!-- Page title with improved typography -->
                        <div class="flex items-center">
                            <h1 class="text-lg font-bold text-gray-800 tracking-tight">{{ $title }}</h1>
                        </div>
                    @endif
                </div>

                <!-- Header: Center - Public navigation (desktop only) -->
                <nav class="hidden lg:flex items-center space-x-8">
                    {{-- Mapa de Entidades --}}
                    <a href="{{ route('public.map.locations') }}"
                        class="flex items-center gap-1.5 text-sm text-gray-600 hover:text-primary transition-colors duration-200 font-medium">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        {{ __('welcome.community_map') }}
                    </a>

                    {{-- Desporto Subaquatico (Dropdown) --}}
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button type="button"
                            class="flex items-center gap-1.5 text-sm text-gray-600 hover:text-primary transition-colors duration-200 font-medium"
                            @click="open = !open">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M18.75 4.236c.982.143 1.954.317 2.916.52A6.003 6.003 0 0 1 16.27 9.728M18.75 4.236V4.5c0 2.108-.966 3.99-2.48 5.228m0 0a6.98 6.98 0 0 1-2.77.952m0 0a6.98 6.98 0 0 1-2.77-.952" />
                            </svg>
                            {{ __('welcome.underwater_sports') }}
                            <svg class="h-3.5 w-3.5 transition-transform duration-200" :class="{ 'rotate-180': open }"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
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
                                class="block px-4 py-2 text-sm text-gray-600 hover:bg-primary/5 hover:text-primary whitespace-nowrap">
                                {{ __('welcome.club_registry') }}
                            </a>
                            <a href="{{ route('public.coach-registry') }}"
                                class="block px-4 py-2 text-sm text-gray-600 hover:bg-primary/5 hover:text-primary whitespace-nowrap">
                                {{ __('welcome.coach_registry') }}
                            </a>
                            <a href="{{ route('public.technical-official-registry') }}"
                                class="block px-4 py-2 text-sm text-gray-600 hover:bg-primary/5 hover:text-primary whitespace-nowrap">
                                {{ __('welcome.technical_official_registry') }}
                            </a>
                        </div>
                    </div>

                    {{-- Mergulho Recreativo e Cientifico (Dropdown) --}}
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button type="button"
                            class="flex items-center gap-1.5 text-sm text-gray-600 hover:text-primary transition-colors duration-200 font-medium"
                            @click="open = !open">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ __('welcome.recreational_scientific_diving') }}
                            <svg class="h-3.5 w-3.5 transition-transform duration-200" :class="{ 'rotate-180': open }"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
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
                                class="block px-4 py-2 text-sm text-gray-600 hover:bg-primary/5 hover:text-primary whitespace-nowrap">
                                {{ __('welcome.diving_service_providers') }}
                            </a>
                            <a href="{{ route('public.diving-professionals') }}"
                                class="block px-4 py-2 text-sm text-gray-600 hover:bg-primary/5 hover:text-primary whitespace-nowrap">
                                {{ __('welcome.diving_professionals') }}
                            </a>
                        </div>
                    </div>
                </nav>

                <!-- Header: Right side -->
                <div class="flex items-center space-x-1 md:space-x-3">

                    <!-- Impersonation bar with improved styling -->
                    <x-impersonation-bar/>

                    <!-- Notifications button with counter -->
                    <livewire:bell-notifications :user="auth()->user()"></livewire:bell-notifications>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Breadcrumb removed as requested -->
</header>
