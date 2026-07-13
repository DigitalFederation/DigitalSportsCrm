<x-guest-layout :title="$title">
    @php($brand = config('branding.primary'))
    @push('head-css')
        {{-- Leaflet CSS --}}
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        {{-- Flag Icons CSS --}}
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7/css/flag-icons.min.css" />
        <style>
            /* Ensure map tiles render correctly */
            .leaflet-container {
                background: #f5f5f5;
            }

            .leaflet-tile-pane {
                opacity: 1 !important;
            }

            /* Frosted glass effects */
            .frosted-glass {
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
            }

            /* Smooth scrolling for anchor navigation */
            html {
                scroll-behavior: smooth;
            }

            /* Card hover effects */
            .entity-card {
                transition: all 0.2s ease-in-out;
            }

            .entity-card:hover {
                transform: translateY(-4px);
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            }

            /* Active tab indicator animation */
            .nav-tab {
                position: relative;
                transition: all 0.2s ease;
            }

            .nav-tab.active::after {
                content: '';
                position: absolute;
                bottom: -1px;
                left: 0;
                right: 0;
                height: 2px;
                background: currentColor;
                border-radius: 2px 2px 0 0;
                animation: tabIndicator 0.2s ease-in-out;
            }

            @keyframes tabIndicator {
                from {
                    transform: scaleX(0.5);
                    opacity: 0.5;
                }

                to {
                    transform: scaleX(1);
                    opacity: 1;
                }
            }

            /* Profile image clip-path for modern shape */
            .profile-clip {
                clip-path: polygon(0% 0%, 100% 0%, 100% 85%, 85% 100%, 0% 100%);
            }
        </style>
    @endpush

    {{-- Modern Fixed Top Navigation Bar --}}
    <header
        class="fixed top-0 left-0 right-0 z-40 h-16 border-b border-gray-200 dark:border-gray-700 bg-white/90 dark:bg-gray-900/90 backdrop-blur-sm shadow-sm">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 h-full flex items-center justify-between relative">
            {{-- Logo (Left) --}}
            <div class="flex items-center">
                <a href="/" title="{{ $brand['short_name'] }} Home" class="flex items-center gap-2">
                    <x-brand-logo class="h-9" text-class="text-base font-bold text-slate-800 mr-2" />
                    <span class="hidden md:inline-block text-sm font-medium text-gray-600 dark:text-gray-300">{{ $brand['short_name'] }}</span>
                </a>
            </div>

            {{-- Navigation Tabs (Center) - Only visible on larger screens --}}
            <nav
                class="hidden md:flex items-center space-x-6 absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2">
                <a href="#overview"
                    class="nav-tab active text-blue-600 dark:text-blue-400 text-sm font-medium hover:text-blue-700 dark:hover:text-blue-300">
                    {{ __('Overview') }}
                </a>
                @if ($hasSportContent)
                    <a href="#desporto-subaquatico"
                        class="nav-tab text-gray-600 dark:text-gray-300 text-sm font-medium hover:text-blue-600 dark:hover:text-blue-400">
                        {{ __('public.entity.underwater_sports') }}
                    </a>
                @endif
                @if ($hasDivingContent)
                    <a href="#mergulho"
                        class="nav-tab text-gray-600 dark:text-gray-300 text-sm font-medium hover:text-blue-600 dark:hover:text-blue-400">
                        {{ __('public.entity.diving') }}
                    </a>
                @endif
            </nav>

            {{-- Login Button with Icon (Right) --}}
            <div class="flex items-center gap-3">
                <button id="shareBtn" type="button"
                    class="inline-flex items-center justify-center p-2 rounded-full text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="18" cy="5" r="3"></circle>
                        <circle cx="6" cy="12" r="3"></circle>
                        <circle cx="18" cy="19" r="3"></circle>
                        <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
                        <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
                    </svg>
                </button>
                <a href="{{ route('login') }}"
                    class="inline-flex items-center gap-2 px-3 py-1.5 border border-transparent text-sm font-medium rounded-md shadow-sm bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                    style="color: #ffffff !important;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                        <polyline points="10 17 15 12 10 7"></polyline>
                        <line x1="15" y1="12" x2="3" y2="12"></line>
                    </svg>
                    <span style="color: #ffffff !important;">{{ __('Login') }}</span>
                </a>
            </div>
        </div>
    </header>

    {{-- Mobile Navigation Menu (Only visible on small screens) --}}
    <div
        class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 shadow-lg">
        <nav class="flex justify-around items-center">
            <a href="#overview" class="flex flex-col items-center py-3 px-2 text-blue-600 dark:text-blue-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="2" y1="12" x2="22" y2="12"></line>
                    <path
                        d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z">
                    </path>
                </svg>
                <span class="text-xs mt-1">{{ __('Overview') }}</span>
            </a>
            @if ($hasSportContent)
                <a href="#desporto-subaquatico"
                    class="flex flex-col items-center py-3 px-2 text-gray-700 dark:text-gray-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <span class="text-xs mt-1">{{ __('Sport') }}</span>
                </a>
            @endif
            @if ($hasDivingContent)
                <a href="#mergulho" class="flex flex-col items-center py-3 px-2 text-gray-700 dark:text-gray-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"></path>
                        <path d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0z">
                        </path>
                    </svg>
                    <span class="text-xs mt-1">{{ __('public.entity.diving') }}</span>
                </a>
            @endif
        </nav>
    </div>

    {{-- Content Container with padding for fixed header --}}
    <div
        class="min-h-screen pt-16 pb-16 md:pb-0 bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800">
        {{-- Hero Section with Modern Design --}}
        <section id="overview" class="relative overflow-hidden">
            {{-- Hero Background with Cover Image Option --}}
            <div
                class="absolute inset-0 bg-gradient-to-br from-blue-800 to-blue-900 dark:from-blue-900 dark:to-blue-950">
                {{-- Use the new backgroundUrl variable --}}
                @if ($backgroundUrl)
                    <div class="absolute inset-0">
                        <img src="{{ $backgroundUrl }}" alt="{{ $entity->name }} Background"
                            class="w-full h-full object-cover opacity-50"> {{-- Adjusted opacity --}}
                        <div class="absolute inset-0 bg-gradient-to-b from-blue-900/30 to-blue-950/70"></div>
                        {{-- Darker overlay --}}
                    </div>
                @else
                    {{-- Background Pattern --}}
                    <div class="absolute inset-0 opacity-10">
                        <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                            <defs>
                                <pattern id="grid" width="8" height="8" patternUnits="userSpaceOnUse">
                                    <path d="M 8 0 L 0 0 0 8" fill="none" stroke="currentColor"
                                        stroke-width="0.5" />
                                </pattern>
                            </defs>
                            <rect width="100" height="100" fill="url(#grid)" />
                        </svg>
                    </div>

                    {{-- Subtle Wave Background --}}
                    <div class="absolute bottom-0 left-0 right-0 h-24 opacity-10">
                        <svg viewBox="0 0 1440 100" fill="none" xmlns="http://www.w3.org/2000/svg"
                            preserveAspectRatio="none">
                            <path d="M0 48.5299C277 78.8299 654 9.8299 1440 48.5299V100H0V48.5299Z"
                                fill="currentColor"></path>
                        </svg>
                    </div>
                @endif
            </div>

            {{-- Hero Content --}}
            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16 md:py-24">
                <div class="flex flex-col md:flex-row items-center gap-8">
                    {{-- Entity Logo/Image with Modern Treatment --}}
                    <div class="flex-shrink-0 relative">
                        {{-- Container for Image and Tag --}}
                        <div class="relative h-52 w-52">
                            {{-- Image Container with Clip --}}
                            <div
                                class="h-full w-full rounded-xl overflow-hidden profile-clip shadow-2xl border-4 border-white/20 bg-gradient-to-br from-blue-100/30 to-blue-300/30 dark:from-blue-900/30 dark:to-blue-700/30 backdrop-blur-sm">
                                <img src="{{ $logoUrl ?: asset('img/user_placeholder.png') }}" {{-- Use logoUrl variable --}}
                                    alt="{{ $entity->name }} Logo" class="h-full w-full object-cover">
                            </div>
                            {{-- international Tag (Moved outside image container, positioned relative to parent) --}}
                            @if ($entity->member_code)
                                <div
                                    class="absolute -bottom-2 -right-2 bg-blue-600 text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg z-10">
                                    {{ __('ID No.') }} {{ $entity->member_code }}
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Entity Info --}}
                    <div class="text-center md:text-left text-white max-w-3xl">
                        <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mb-2 text-white drop-shadow-lg [text-shadow:_2px_2px_8px_rgb(0_0_0_/_80%)]">
                            {{ $entity->name }}
                        </h1>
                        <p class="text-lg md:text-xl text-white mb-1 drop-shadow-md [text-shadow:_1px_1px_4px_rgb(0_0_0_/_50%)]">
                            {{ $entity->location }}, {{ $entity->country->name }}
                        </p>
                        @if ($entity->website)
                            <a href="{{ $entity->website }}" target="_blank" rel="noopener noreferrer"
                                class="text-sm transition-colors inline-flex items-center gap-1 underline underline-offset-2"
                                style="color: #ffffff !important; text-shadow: 1px 1px 3px rgba(0,0,0,0.5);">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.72"></path>
                                    <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.72-1.72"></path>
                                </svg>
                                {{ $entity->website }}
                            </a>
                        @endif

                        {{-- Display Public Description --}}
                        @if ($entity->public_description)
                            <div
                                class="mt-4 text-white text-base leading-relaxed max-w-2xl mx-auto md:mx-0 drop-shadow-md [text-shadow:_1px_1px_3px_rgb(0_0_0_/_40%)]">
                                {!! $entity->public_description !!}
                            </div>
                        @endif

                        {{-- Contact/Social Buttons (Optional) --}}
                        <div class="mt-6 flex flex-wrap justify-center md:justify-start gap-3">
                            @if ($entity->email)
                                <a href="mailto:{{ $entity->email }}"
                                    class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-white/90 hover:bg-white text-gray-800 shadow-md transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                                        <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                                    </svg>
                                    <span class="font-medium">{{ $entity->email }}</span>
                                </a>
                            @endif
                            @if ($entity->phone)
                                <a href="tel:{{ $entity->phone }}"
                                    class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-white/90 hover:bg-white text-gray-800 shadow-md transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path
                                            d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z">
                                        </path>
                                    </svg>
                                    <span class="font-medium">{{ $entity->phone }}</span>
                                </a>
                            @endif
                            @if ($entity->website)
                                <a href="{{ Str::startsWith($entity->website, ['http://', 'https://']) ? $entity->website : 'http://' . $entity->website }}"
                                    target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-white/90 hover:bg-white text-gray-800 shadow-md transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="2" y1="12" x2="22" y2="12"></line>
                                        <path
                                            d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z">
                                        </path>
                                    </svg>
                                    <span class="font-medium">{{ parse_url($entity->website, PHP_URL_HOST) ?? $entity->website }}</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </section>

        {{-- Main Content Area with Modern Card Layout --}}
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12 md:py-16">
            {{-- Entity Details Card with Map: More Compact Layout --}}
            <section class="mb-12">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-400"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21V5a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v16"></path>
                        <path d="M1 21h22"></path>
                        <path d="M9 9h6"></path>
                        <path d="M9 13h6"></path>
                        <path d="M9 17h6"></path>
                    </svg>
                    {{ __('About Us') }}
                </h2>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {{-- Entity Details Card (Now Narrower) --}}
                    <div class="lg:col-span-1">
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden h-full">
                            <div class="p-6">
                                <div class="space-y-4">
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                            {{ __('Full Address') }}</h3>
                                        <address class="mt-1 text-base not-italic text-gray-900 dark:text-gray-200">
                                            {{ $entity->address }}<br>
                                            {{ $entity->postal_code }} {{ $entity->location }}
                                            @if ($entity->country)
                                                <br>{{ $entity->country->name }}
                                            @endif
                                        </address>
                                    </div>

                                    @if ($entity->legal_responsible_person)
                                        <div>
                                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                {{ __('Legal Representative') }}</h3>
                                            <p class="mt-1 text-base text-gray-900 dark:text-gray-200">
                                                {{ $entity->legal_responsible_person }}</p>
                                        </div>
                                    @endif
                                </div>

                                {{-- Call To Action Buttons --}}
                                <div class="mt-6 flex flex-wrap gap-3">
                                    @if ($entity->email)
                                        <a href="mailto:{{ $entity->email }}"
                                            class="inline-flex items-center gap-2 px-4 py-2 border border-transparent rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="2" y="4" width="20" height="16" rx="2">
                                                </rect>
                                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                                            </svg>
                                            <span class="text-white">{{ __('Contact Us') }}</span>
                                        </a>
                                    @endif

                                    @if ($entity->website)
                                        <a href="{{ Str::startsWith($entity->website, ['http://', 'https://']) ? $entity->website : 'http://' . $entity->website }}"
                                            target="_blank" rel="noopener noreferrer"
                                            class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6">
                                                </path>
                                                <polyline points="15 3 21 3 21 9"></polyline>
                                                <line x1="10" y1="14" x2="21" y2="3">
                                                </line>
                                            </svg>
                                            {{ __('Visit Website') }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Map Card (Wider) --}}
                    <div class="lg:col-span-2">
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden h-full">
                            @if ($entity->lat && $entity->lng)
                                <div class="relative h-80 overflow-hidden">
                                    <div id="entityMap" class="h-full w-full"></div>
                                    <div
                                        class="absolute top-2 right-2 bg-white dark:bg-gray-800 rounded-md shadow-md p-1">
                                        <a href="https://maps.google.com/?q={{ $entity->lat }},{{ $entity->lng }}"
                                            target="_blank" rel="noopener noreferrer"
                                            class="inline-flex items-center justify-center p-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                                                <polyline points="10 17 15 12 10 7"></polyline>
                                                <line x1="15" y1="12" x2="3" y2="12">
                                                </line>
                                            </svg>
                                            <span class="ml-1">{{ __('Open in Maps') }}</span>
                                        </a>
                                    </div>
                                </div>
                            @else
                                <div class="flex flex-col items-center justify-center p-8 h-full">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="h-16 w-16 text-gray-300 dark:text-gray-600 mb-4" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M17.5 7.5c.28 0 .5.22.5.5s-.22.5-.5.5-.5-.22-.5-.5.22-.5.5-.5z"></path>
                                        <path d="M5.5 15.5c.28 0 .5.22.5.5s-.22.5-.5.5-.5-.22-.5-.5.22-.5.5-.5z"></path>
                                        <path
                                            d="M11.5 3.5c2.14 0 3.5 1.36 3.5 3.5A3.5 3.5 0 0 1 11.5 10c-2.14 0-3.5-1.36-3.5-3.5S9.36 3.5 11.5 3.5zM11.5 19.5c2.14 0 3.5 1.36 3.5 3.5 0 .86-.34 1.69-.94 2.3a3.46 3.46 0 0 1-5.12 0c-.6-.61-.94-1.44-.94-2.3 0-2.14 1.36-3.5 3.5-3.5z">
                                        </path>
                                        <path
                                            d="M10.88 14.35c-3.2-.7-5.6-3.5-5.9-6.84M12.12 14.35c3.2-.7 5.6-3.5 5.9-6.84">
                                        </path>
                                        <path d="M11.5 13.5 7.5 17.5M11.5 13.5l4 4"></path>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                        {{ __('Location Not Available') }}</h3>
                                    <p class="mt-2 text-gray-500 dark:text-gray-400 text-center max-w-md">
                                        {{ __('The exact location for this entity has not been specified. Contact them directly for detailed directions.') }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </section>

            {{-- Section 2: Underwater Sports (Desporto Subaquatico) --}}
            @if ($hasSportContent)
                <section id="desporto-subaquatico" class="mb-12 scroll-mt-20">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-400"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        {{ __('public.entity.underwater_sports') }}
                    </h2>

                    {{-- Sport Licenses --}}
                    @if ($sportLicenses->isNotEmpty())
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                                {{ __('public.entity.sport_licenses') }}
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach ($sportLicenses as $attributedLicense)
                                    <div
                                        class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden entity-card border border-gray-100 dark:border-gray-700 flex flex-col">
                                        <div class="px-6 py-5 flex-grow">
                                            <div class="flex items-start justify-between mb-3">
                                                <div class="flex-1 mr-3">
                                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">
                                                        {{ $attributedLicense->license->name }}</h4>
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                                        {{ __('Active') }}
                                                    </span>
                                                </div>
                                                @if ($attributedLicense->license->getFirstMediaUrl('logo', 'thumb'))
                                                    <div
                                                        class="flex-shrink-0 h-12 w-12 bg-white dark:bg-gray-800 rounded-md p-1 flex items-center justify-center shadow-inner border border-gray-200 dark:border-gray-700">
                                                        <img src="{{ $attributedLicense->license->getFirstMediaUrl('logo', 'thumb') }}"
                                                            alt="{{ $attributedLicense->license->name }} Logo"
                                                            class="max-h-full max-w-full object-contain rounded-sm">
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="text-sm text-gray-600 dark:text-gray-300 space-y-1">
                                                @if ($attributedLicense->current_term_starts_at && $attributedLicense->current_term_ends_at)
                                                    <p class="flex items-center gap-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="h-4 w-4 text-gray-400" viewBox="0 0 24 24"
                                                            fill="none" stroke="currentColor" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round">
                                                            <rect x="3" y="4" width="18" height="18"
                                                                rx="2" ry="2"></rect>
                                                            <line x1="16" y1="2" x2="16"
                                                                y2="6"></line>
                                                            <line x1="8" y1="2" x2="8"
                                                                y2="6"></line>
                                                            <line x1="3" y1="10" x2="21"
                                                                y2="10"></line>
                                                        </svg>
                                                        {{ $attributedLicense->current_term_starts_at->format('M d, Y') }}
                                                        -
                                                        {{ $attributedLicense->current_term_ends_at->format('M d, Y') }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Coaches --}}
                    @if ($coaches->isNotEmpty())
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                                {{ __('public.entity.coaches') }}
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                @foreach ($coaches as $coach)
                                    <div
                                        class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden entity-card border border-gray-100 dark:border-gray-700">
                                        <div class="p-6 text-center">
                                            <div
                                                class="mx-auto h-20 w-20 rounded-full overflow-hidden mb-4 bg-gray-100 dark:bg-gray-700 border-2 border-white dark:border-gray-600 shadow-lg">
                                                @if ($coach['avatar_url'])
                                                    <img class="h-full w-full object-cover"
                                                        src="{{ $coach['avatar_url'] }}" alt="{{ $coach['name'] }}">
                                                @else
                                                    <div
                                                        class="h-full w-full flex items-center justify-center bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-200 font-bold text-xl">
                                                        {{ strtoupper(substr($coach['name'], 0, 2)) }}
                                                    </div>
                                                @endif
                                            </div>
                                            <h4 class="text-base font-semibold text-gray-900 dark:text-white">
                                                {{ $coach['name'] }}</h4>
                                            @if ($coach['sport'])
                                                <p class="mt-1 text-sm text-blue-600 dark:text-blue-400">
                                                    {{ $coach['sport'] }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </section>
            @endif

            {{-- Section 3: Diving (Mergulho) --}}
            @if ($hasDivingContent)
                <section id="mergulho" class="mb-12 scroll-mt-20">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-400"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"></path>
                            <path d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0z">
                            </path>
                        </svg>
                        {{ __('public.entity.diving') }}
                    </h2>

                    {{-- Diving Licenses --}}
                    @if ($divingLicenses->isNotEmpty())
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                                {{ __('public.entity.diving_licenses') }}
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach ($divingLicenses as $attributedLicense)
                                    <div
                                        class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden entity-card border border-gray-100 dark:border-gray-700 flex flex-col">
                                        <div class="px-6 py-5 flex-grow">
                                            <div class="flex items-start justify-between mb-3">
                                                <div class="flex-1 mr-3">
                                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">
                                                        {{ $attributedLicense->license->name }}</h4>
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                                        {{ __('Active') }}
                                                    </span>
                                                </div>
                                                @if ($attributedLicense->license->getFirstMediaUrl('logo', 'thumb'))
                                                    <div
                                                        class="flex-shrink-0 h-12 w-12 bg-white dark:bg-gray-800 rounded-md p-1 flex items-center justify-center shadow-inner border border-gray-200 dark:border-gray-700">
                                                        <img src="{{ $attributedLicense->license->getFirstMediaUrl('logo', 'thumb') }}"
                                                            alt="{{ $attributedLicense->license->name }} Logo"
                                                            class="max-h-full max-w-full object-contain rounded-sm">
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="text-sm text-gray-600 dark:text-gray-300 space-y-1">
                                                @if ($attributedLicense->current_term_starts_at && $attributedLicense->current_term_ends_at)
                                                    <p class="flex items-center gap-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="h-4 w-4 text-gray-400" viewBox="0 0 24 24"
                                                            fill="none" stroke="currentColor" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round">
                                                            <rect x="3" y="4" width="18" height="18"
                                                                rx="2" ry="2"></rect>
                                                            <line x1="16" y1="2" x2="16"
                                                                y2="6"></line>
                                                            <line x1="8" y1="2" x2="8"
                                                                y2="6"></line>
                                                            <line x1="3" y1="10" x2="21"
                                                                y2="10"></line>
                                                        </svg>
                                                        {{ $attributedLicense->current_term_starts_at->format('M d, Y') }}
                                                        -
                                                        {{ $attributedLicense->current_term_ends_at->format('M d, Y') }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Diving Professionals --}}
                    @if ($divingProfessionals->isNotEmpty())
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
                                {{ __('public.entity.diving_professionals') }}
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                @foreach ($divingProfessionals as $professional)
                                    <div
                                        class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden entity-card border border-gray-100 dark:border-gray-700">
                                        <div class="p-6 text-center">
                                            <div
                                                class="mx-auto h-20 w-20 rounded-full overflow-hidden mb-4 bg-gray-100 dark:bg-gray-700 border-2 border-white dark:border-gray-600 shadow-lg">
                                                @if ($professional['avatar_url'])
                                                    <img class="h-full w-full object-cover"
                                                        src="{{ $professional['avatar_url'] }}"
                                                        alt="{{ $professional['name'] }}">
                                                @else
                                                    <div
                                                        class="h-full w-full flex items-center justify-center bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-200 font-bold text-xl">
                                                        {{ strtoupper(substr($professional['name'], 0, 2)) }}
                                                    </div>
                                                @endif
                                            </div>
                                            <h4 class="text-base font-semibold text-gray-900 dark:text-white">
                                                {{ $professional['name'] }}</h4>
                                            @if ($professional['role'])
                                                <p class="mt-1 text-sm text-blue-600 dark:text-blue-400">
                                                    {{ $professional['role'] }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </section>
            @endif
        </div>

        {{-- Footer --}}
        <footer class="bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row items-center justify-between">
                    <div class="flex items-center mb-4 md:mb-0">
                        <x-brand-logo class="h-8 mr-2" text-class="text-base font-bold text-slate-800 mr-2" />
                        <p class="text-sm text-gray-600 dark:text-gray-400">© {{ date('Y') }} {{ $brand['short_name'] }}.
                            {{ __('All rights reserved.') }}</p>
                    </div>

                    <div class="flex space-x-6">
                        <a href="#"
                            class="text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400">
                            {{ __('Privacy Policy') }}
                        </a>
                        <a href="#"
                            class="text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400">
                            {{ __('Terms of Service') }}
                        </a>
                        <a href="#"
                            class="text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400">
                            {{ __('Contact') }}
                        </a>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    {{-- Floating Action Button for Contact --}}
    @if ($entity->email || $entity->phone)
        <div class="fixed bottom-24 md:bottom-6 right-6 z-30">
            <div class="relative group">
                {{-- Main FAB Button --}}
                <button type="button" id="contactFabBtn"
                    class="flex items-center justify-center h-14 w-14 rounded-full bg-blue-600 text-white shadow-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path
                            d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z">
                        </path>
                    </svg>
                </button>

                {{-- FAB Options Menu (Hidden by default) --}}
                <div id="fabMenu"
                    class="absolute bottom-full right-0 mb-3 hidden flex-col items-end space-y-2 transition-all group-focus-within:flex">
                    @if ($entity->phone)
                        <a href="tel:{{ $entity->phone }}"
                            class="flex items-center justify-center gap-2 pl-3 pr-4 py-2 bg-white dark:bg-gray-800 rounded-full shadow-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <span class="bg-blue-100 dark:bg-blue-900 p-2 rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path
                                        d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z">
                                    </path>
                                </svg>
                            </span>
                            {{ __('Call') }}
                        </a>
                    @endif

                    @if ($entity->email)
                        <a href="mailto:{{ $entity->email }}"
                            class="flex items-center justify-center gap-2 pl-3 pr-4 py-2 bg-white dark:bg-gray-800 rounded-full shadow-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <span class="bg-blue-100 dark:bg-blue-900 p-2 rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                                </svg>
                            </span>
                            {{ __('Email') }}
                        </a>
                    @endif

                    @if ($entity->website)
                        <a href="{{ Str::startsWith($entity->website, ['http://', 'https://']) ? $entity->website : 'http://' . $entity->website }}"
                            target="_blank" rel="noopener noreferrer"
                            class="flex items-center justify-center gap-2 pl-3 pr-4 py-2 bg-white dark:bg-gray-800 rounded-full shadow-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <span class="bg-blue-100 dark:bg-blue-900 p-2 rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <path d="M2 12h20"></path>
                                    <path
                                        d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z">
                                    </path>
                                </svg>
                            </span>
                            {{ __('Website') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Share Modal (Hidden by default) --}}
    <div id="shareModal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
        <div class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur-sm" id="modalBackdrop"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('Share This Entity') }}</h3>
                    <button type="button" id="closeModalBtn"
                        class="text-gray-400 hover:text-gray-500 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            <div class="px-6 py-4">
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <button id="copyLinkBtn"
                        class="flex flex-col items-center justify-center p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-500 dark:text-gray-400 mb-2"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                        </svg>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ __('Copy Link') }}</span>
                    </button>

                    <button id="shareWhatsAppBtn"
                        class="flex flex-col items-center justify-center p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-500 mb-2"
                            viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.894 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.626.712.227 1.36.195 1.871.118.571-.078 1.757-.719 2.006-1.413.248-.695.248-1.29.173-1.414z" />
                        </svg>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ __('WhatsApp') }}</span>
                    </button>

                    <button id="shareFacebookBtn"
                        class="flex flex-col items-center justify-center p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600 mb-2"
                            viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z" />
                        </svg>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ __('Facebook') }}</span>
                    </button>

                    <button id="shareTwitterBtn"
                        class="flex flex-col items-center justify-center p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-400 mb-2"
                            viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z" />
                        </svg>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ __('Twitter') }}</span>
                    </button>
                </div>

                <div class="relative">
                    <input type="text" id="shareUrlInput" value="{{ url()->current() }}"
                        class="block w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white text-sm"
                        readonly>
                    <button id="copyInputBtn"
                        class="absolute right-2 top-1/2 transform -translate-y-1/2 p-1 rounded-md text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                            <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-right">
                <button type="button" id="closeModalBtn2"
                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-md transition-colors">
                    {{ __('Close') }}
                </button>
            </div>
        </div>
    </div>

    @push('footer-scripts')
        {{-- Leaflet JS --}}
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                console.log('DOMContentLoaded fired!'); // <-- Moved log here

                // Declare shared variables once
                const entityName = "{{ addslashes($entity->name) }}";

                // Navigation tab handling for desktop
                const navTabs = document.querySelectorAll('.nav-tab');

                function updateActiveTab() {
                    const scrollPosition = window.scrollY;

                    // Get all section positions (dynamic based on which sections exist)
                    const sections = [
                        document.getElementById('overview'),
                        document.getElementById('desporto-subaquatico'),
                        document.getElementById('mergulho')
                    ].filter(Boolean); // Remove null entries for sections that don't exist

                    // Find the section that's currently in view
                    let activeIndex = 0;
                    sections.forEach((section, index) => {
                        if (section) {
                            const sectionTop = section.offsetTop - 100;
                            const sectionBottom = sectionTop + section.offsetHeight;

                            if (scrollPosition >= sectionTop && scrollPosition < sectionBottom) {
                                activeIndex = index;
                            }
                        }
                    });

                    // Update tab styles
                    navTabs.forEach((tab, index) => {
                        if (index === activeIndex) {
                            tab.classList.add('active');
                            tab.classList.add('text-blue-600');
                            tab.classList.add('dark:text-blue-400');
                            tab.classList.remove('text-gray-600');
                            tab.classList.remove('dark:text-gray-300');
                        } else {
                            tab.classList.remove('active');
                            tab.classList.remove('text-blue-600');
                            tab.classList.remove('dark:text-blue-400');
                            tab.classList.add('text-gray-600');
                            tab.classList.add('dark:text-gray-300');
                        }
                    });

                    // Also update mobile navigation
                    const mobileNavs = document.querySelectorAll('.md\\:hidden nav a');
                    mobileNavs.forEach((nav, index) => {
                        if (index === activeIndex) {
                            nav.classList.add('text-blue-600');
                            nav.classList.add('dark:text-blue-400');
                            nav.classList.remove('text-gray-500');
                            nav.classList.remove('dark:text-gray-400');
                        } else {
                            nav.classList.remove('text-blue-600');
                            nav.classList.remove('dark:text-blue-400');
                            nav.classList.add('text-gray-500');
                            nav.classList.add('dark:text-gray-400');
                        }
                    });
                }

                // Update active tab on scroll
                window.addEventListener('scroll', updateActiveTab);
                updateActiveTab(); // Run once on page load
                console.log('updateActiveTab');
                // Initialize map if coordinates exist
                @if ($entity->lat && $entity->lng)
                    const lat = {{ $entity->lat }};
                    const lng = {{ $entity->lng }};
                    // const entityName = "{{ addslashes($entity->name) }}"; // Removed duplicate
                    const address = "{{ addslashes($entity->address) }}";
                    const location = "{{ addslashes($entity->location) }}";
                    const mapElement = document.getElementById('entityMap');

                    if (mapElement) {
                        const map = L.map(mapElement, {
                            scrollWheelZoom: false,
                            zoomControl: true,
                            attributionControl: false, // We'll add custom attribution
                        }).setView([lat, lng], 14); // Slightly closer zoom

                        // Use a cleaner map style
                        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                            maxZoom: 19,
                            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
                        }).addTo(map);

                        // Invalidate size immediately in case container is ready
                        map.invalidateSize();

                        // Add small, non-obtrusive attribution
                        L.control.attribution({
                            position: 'bottomright',
                            prefix: '<a href="https://leafletjs.com">Leaflet</a>'
                        }).addTo(map);

                        // Custom marker icon with diving theme
                        const blueIcon = L.divIcon({
                            className: 'custom-div-icon',
                            html: `
                            <div class="marker-pin bg-blue-600 shadow-lg">
                                <span class="text-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M19.07 4.93a10 10 0 0 0-16.28 11 1.06 1.06 0 0 1 .09.64L2 20.8a1 1 0 0 0 .27.91A1 1 0 0 0 3 22h.2l4.28-.86a1.26 1.26 0 0 1 .64.09 10 10 0 0 0 11-16.28zM8 13a1 1 0 1 1 1-1 1 1 0 0 1-1 1zm4 0a1 1 0 1 1 1-1 1 1 0 0 1-1 1zm4 0a1 1 0 1 1 1-1 1 1 0 0 1-1 1z"/>
                                    </svg>
                                </span>
                            </div>
                        `,
                            iconSize: [30, 42],
                            iconAnchor: [15, 42]
                        });

                        // Add the marker with our custom icon
                        const marker = L.marker([lat, lng], {
                            icon: blueIcon
                        }).addTo(map);

                        // Create a nicer looking popup
                        const popupContent = `
                        <div class="entity-popup">
                            <div class="font-bold text-blue-800">${entityName}</div>
                            <div class="text-gray-700 text-sm mt-1">${address}</div>
                            <div class="text-gray-700 text-sm">${location}</div>
                        </div>
                    `;

                        marker.bindPopup(popupContent).openPopup();

                        // Add marker pin styling
                        const style = document.createElement('style');
                        style.textContent = `
                        .marker-pin {
                            width: 30px;
                            height: 30px;
                            border-radius: 50% 50% 50% 0;
                            position: absolute;
                            transform: rotate(-45deg);
                            left: 50%;
                            top: 50%;
                            margin: -15px 0 0 -15px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        }

                        .marker-pin span {
                            transform: rotate(45deg);
                        }

                        .entity-popup {
                            padding: 4px 8px;
                        }
                    `;
                        document.head.appendChild(style);

                        // Force map to redraw/invalidate size after initialization
                        // This often fixes issues where tiles don't load initially
                        setTimeout(function() {
                            map.invalidateSize();
                        }, 100); // Small delay to ensure DOM is ready
                    }
                @endif

                // Share Modal Functionality
                const shareBtn = document.getElementById('shareBtn');
                const shareModal = document.getElementById('shareModal');
                const modalBackdrop = document.getElementById('modalBackdrop');
                const closeModalBtn = document.getElementById('closeModalBtn');
                const closeModalBtn2 = document.getElementById('closeModalBtn2');
                const copyLinkBtn = document.getElementById('copyLinkBtn');
                const shareWhatsAppBtn = document.getElementById('shareWhatsAppBtn');
                const shareFacebookBtn = document.getElementById('shareFacebookBtn');
                const shareTwitterBtn = document.getElementById('shareTwitterBtn');
                const shareUrlInput = document.getElementById('shareUrlInput');
                const copyInputBtn = document.getElementById('copyInputBtn');

                const shareUrl = window.location.href;
                // const entityName = "{{ addslashes($entity->name) }}"; // Removed duplicate
                const shareText = `{{ __('Check out this entity on :portal:', ['portal' => config('branding.primary.portal_name', 'Digital Sports CRM')]) }} ${entityName}`;
                const shareTitle = document.title;

                // Toggle modal
                shareBtn.addEventListener('click', () => {
                    shareModal.classList.remove('hidden');
                });

                modalBackdrop.addEventListener('click', () => {
                    shareModal.classList.add('hidden');
                });

                closeModalBtn.addEventListener('click', () => {
                    shareModal.classList.add('hidden');
                });

                closeModalBtn2.addEventListener('click', () => {
                    shareModal.classList.add('hidden');
                });

                // Copy link functionality
                copyLinkBtn.addEventListener('click', async () => {
                    try {
                        await navigator.clipboard.writeText(shareUrl);
                        showToast("{{ __('Link copied to clipboard!') }}");
                    } catch (err) {
                        console.error('Failed to copy: ', err);
                    }
                });

                copyInputBtn.addEventListener('click', async () => {
                    try {
                        await navigator.clipboard.writeText(shareUrlInput.value);
                        showToast("{{ __('Link copied to clipboard!') }}");
                    } catch (err) {
                        console.error('Failed to copy: ', err);
                    }
                });

                // Social media share buttons
                shareWhatsAppBtn.addEventListener('click', () => {
                    const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(shareText + ' ' + shareUrl)}`;
                    window.open(whatsappUrl, '_blank', 'noopener,noreferrer');
                });

                shareFacebookBtn.addEventListener('click', () => {
                    const facebookUrl =
                        `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`;
                    window.open(facebookUrl, '_blank', 'noopener,noreferrer');
                });

                shareTwitterBtn.addEventListener('click', () => {
                    const twitterUrl =
                        `https://twitter.com/intent/tweet?text=${encodeURIComponent(shareText)}&url=${encodeURIComponent(shareUrl)}`;
                    window.open(twitterUrl, '_blank', 'noopener,noreferrer');
                });

                // Toast notification function
                function showToast(message) {
                    // Create toast element
                    const toast = document.createElement('div');
                    toast.className =
                        'fixed bottom-4 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center';
                    toast.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        ${message}
                    `;

                    // Add to DOM
                    document.body.appendChild(toast);

                    // Remove after 3 seconds
                    setTimeout(() => {
                        toast.style.opacity = '0';
                        toast.style.transition = 'opacity 0.5s ease';
                        setTimeout(() => {
                            document.body.removeChild(toast);
                        }, 500);
                    }, 3000);
                }

                // Floating Action Button Functionality
                const contactFabBtn = document.getElementById('contactFabBtn');
                const fabMenu = document.getElementById('fabMenu');

                if (contactFabBtn && fabMenu) {
                    // Toggle FAB menu on button click
                    contactFabBtn.addEventListener('click', () => {
                        fabMenu.classList.toggle('hidden');
                    });

                    // Close FAB menu when clicking outside
                    document.addEventListener('click', (event) => {
                        if (!contactFabBtn.contains(event.target) && !fabMenu.contains(event.target)) {
                            fabMenu.classList.add('hidden');
                        }
                    });
                }
            });
        </script>
    @endpush
</x-guest-layout>
