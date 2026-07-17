{{-- resources/views/welcome.blade.php --}}

<x-public-layout>
    @php
        $brand = config('branding.primary');
    @endphp
    <div class="min-h-screen bg-slate-50">
        {{-- Shared Header Component --}}
        <x-public.header currentPage="home" />

        <!-- ───────────────────────────────────────────
              HERO + REGISTRATION SECTION
        ──────────────────────────────────────────── -->
        <section class="text-white" style="background-color: #213165;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20">
                <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">

                    <!-- Left Column: Hero Content -->
                    <div class="text-center lg:text-left">
                        <h1 class="text-4xl lg:text-5xl xl:text-6xl font-bold mb-6 leading-tight tracking-tight text-white">
                            {{ __('welcome.federation_portal') }}
                            <span class="block text-xl lg:text-2xl font-normal text-blue-200 mt-3">
                                {{ $brand['name'] }}
                            </span>
                        </h1>
                    </div>

                    <!-- Right Column: Registration Cards -->
                    <div class="space-y-4">
                        <!-- Individual Card -->
                        <a href="{{ route('public.individual.create') }}"
                           class="group block bg-white rounded-xl p-5 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-transparent hover:border-cyan-500">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center" style="background-color: #0f90c8;">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <h3 class="text-lg font-bold text-slate-900">{{ __('welcome.individual_account') }}</h3>
                                        <svg class="w-5 h-5 text-cyan-600 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                        </svg>
                                    </div>
                                    <p class="text-slate-600 text-sm mb-3">{{ __('welcome.individual_subtitle') }}</p>
                                    <div class="flex flex-wrap gap-1.5">
                                        <span class="inline-flex items-center px-2 py-0.5 bg-cyan-100 rounded text-xs font-medium text-cyan-700">
                                            {{ __('welcome.athletes_coaches') }}
                                        </span>
                                        <span class="inline-flex items-center px-2 py-0.5 bg-cyan-100 rounded text-xs font-medium text-cyan-700">
                                            {{ __('welcome.divers_instructors') }}
                                        </span>
                                        <span class="inline-flex items-center px-2 py-0.5 bg-cyan-100 rounded text-xs font-medium text-cyan-700">
                                            {{ __('welcome.scientific_divers') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>

                        <!-- Organization Card -->
                        <a href="{{ route('entity.registration.form') }}"
                           class="group block bg-white rounded-xl p-5 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-transparent hover:border-emerald-500">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 w-12 h-12 bg-emerald-600 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <h3 class="text-lg font-bold text-slate-900">{{ __('welcome.organisation_account') }}</h3>
                                        <svg class="w-5 h-5 text-emerald-600 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                        </svg>
                                    </div>
                                    <p class="text-slate-600 text-sm mb-3">{{ __('welcome.organisation_subtitle') }}</p>
                                    <div class="flex flex-wrap gap-1.5">
                                        <span class="inline-flex items-center px-2 py-0.5 bg-emerald-100 rounded text-xs font-medium text-emerald-700">
                                            {{ __('welcome.diving_clubs') }}
                                        </span>
                                        <span class="inline-flex items-center px-2 py-0.5 bg-emerald-100 rounded text-xs font-medium text-emerald-700">
                                            {{ __('welcome.diving_schools') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- ───────────────────────────────────────────
              FOOTER SECTION
        ──────────────────────────────────────────── -->
        <footer class="bg-slate-800 text-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid lg:grid-cols-4 gap-8 mb-8">
                    <!-- About federation -->
                    <div class="lg:col-span-2">
                        <div class="flex items-center mb-4">
                            <x-brand-logo class="h-10 w-auto mr-3" text-class="hidden" />
                            <div>
                                <div class="text-lg font-bold">{{ $brand['short_name'] }}</div>
                                <div class="text-sm text-slate-400">{{ $brand['name'] }}</div>
                            </div>
                        </div>
                        <p class="text-slate-300 text-sm leading-relaxed mb-4 max-w-md">
                            {{ __('welcome.about_federation') }}
                        </p>
                    </div>

                    <!-- Quick Links -->
                    <div>
                        <h3 class="text-base font-bold mb-4 text-white">{{ __('welcome.quick_links') }}</h3>
                        <ul class="space-y-2">
                            <li>
                                <a href="{{ $brand['website_url'] }}" target="_blank" class="text-slate-300 hover:text-white transition-colors text-sm">
                                    {{ __('welcome.official_website') }}
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('public.map.locations') }}" class="text-slate-300 hover:text-white transition-colors text-sm">
                                    {{ __('welcome.community_map') }}
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Contact -->
                    <div>
                        <h3 class="text-base font-bold mb-4 text-white">{{ __('welcome.contact') }}</h3>
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <svg class="w-4 h-4 mr-2 text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <div>
                                    <div class="text-slate-300 text-sm">{{ __('welcome.address') }}</div>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <svg class="w-4 h-4 mr-2 text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <div>
                                    <div class="text-slate-300 text-sm">{{ __('welcome.phone') }}</div>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <svg class="w-4 h-4 mr-2 text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                <div>
                                    <div class="text-slate-300 text-sm">{{ __('welcome.mobile') }}</div>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <svg class="w-4 h-4 mr-2 text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <div>
                                    <div class="text-slate-300 text-sm">{{ __('welcome.email_address') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Section -->
                <div class="pt-6 border-t border-slate-700">
                    <div class="flex flex-col md:flex-row justify-between items-center">
                        <div class="text-slate-400 text-sm mb-4 md:mb-0">
                            &copy; {{ date('Y') }} {{ $brand['short_name'] }} - {{ $brand['name'] }}. {{ __('welcome.all_rights') }}
                        </div>
                        <div class="flex items-center space-x-6 text-sm">
                            <a href="{{ route('privacy-policy') }}" class="text-slate-400 hover:text-slate-300 transition-colors">Política de Privacidade</a>
                            <a href="{{ route('terms-of-service') }}" class="text-slate-400 hover:text-slate-300 transition-colors">Termos de Uso</a>
                            <a href="mailto:{{ \App\Models\SiteSetting::get('federation_support_email', $brand['support_email']) }}" class="text-slate-400 hover:text-slate-300 transition-colors">Suporte</a>
                        </div>
                    </div>
                </div>
            </div>
        </footer>

        <!-- ───────── LOGIN MODAL ───────── -->
        <div x-cloak
             x-show="$store.loginOpen.on"
             class="fixed inset-0 z-50 flex items-center justify-center"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="$store.loginOpen.toggle()"></div>

            <!-- Modal -->
            <div class="relative w-full sm:max-w-md p-6">
                <div class="card bg-white rounded-lg shadow-xl">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold text-gray-900">{{ __('welcome.login') }}</h2>
                        <button @click="$store.loginOpen.toggle()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <x-validation-errors class="mb-4" />

                    @if (session('status'))
                        <div class="mb-4 font-medium text-sm text-green-600">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div>
                            <x-label for="email" value="{{ __('welcome.email') }}" />
                            <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
                        </div>

                        <div class="mt-4">
                            <x-label for="password" value="{{ __('welcome.password') }}" />
                            <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
                        </div>

                        <div class="block mt-4">
                            <label for="remember_me" class="flex items-center">
                                <x-checkbox id="remember_me" name="remember" />
                                <span class="ml-2 text-sm text-gray-600">{{ __('welcome.remember_me') }}</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-between mt-4">
                            @if (Route::has('password.request'))
                                <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('password.request') }}">
                                    {{ __('welcome.forgot_password') }}
                                </a>
                            @endif

                            <button type="submit" class="btn btn-primary px-4">
                                {{ __('welcome.log_in') }}
                            </button>
                        </div>
                    </form>

                    <div class="mt-6 pt-4 border-t border-gray-200">
                        <p class="text-sm text-gray-600 text-center">
                            {{ __('welcome.need_account') }}
                            <a href="{{ route('public.individual.create') }}" class="text-blue-600 hover:text-blue-800">
                                {{ __('welcome.register_here') }}
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Alpine store for login modal -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('loginOpen', {
                on: @json($errors->any()),
                toggle() { this.on = !this.on }
            })
        })
    </script>
</x-public-layout>
