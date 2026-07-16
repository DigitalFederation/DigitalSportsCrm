{{-- resources/views/welcome.blade.php --}}

<x-public-layout :language-switcher="false">
    @php
        $brand = config('branding.primary');
    @endphp
    <div class="min-h-screen bg-slate-50 flex flex-col">
        {{-- Shared Header Component --}}
        <x-public.header currentPage="home" />

        <!-- ───────────────────────────────────────────
              HERO + REGISTRATION SECTION
        ──────────────────────────────────────────── -->
        @php
            $heroBackground = \App\Models\SiteSetting::get('hero_background_path');
        @endphp
        <section class="relative flex-1 flex items-center text-white bg-cover bg-center"
                 style="background-color: #213165;@if($heroBackground) background-image: url('{{ asset($heroBackground) }}');@endif">
            @if ($heroBackground)
                <div class="absolute inset-0 bg-slate-900/60"></div>
            @endif
            <div class="relative w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
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
        <footer class="bg-slate-800 text-white py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-4">
                    <!-- About federation -->
                    <div class="flex items-center mb-2">
                        <img src="{{ asset($brand['logo_path']) }}" class="h-8 w-auto mr-3" alt="{{ $brand['short_name'] }} Logo">
                        <div>
                            <div class="text-sm text-slate-400">{{ $brand['name'] }}</div>
                        </div>
                    </div>
                    <p class="text-slate-300 text-sm leading-relaxed max-w-md">
                        {{ __('welcome.about_federation') }}
                    </p>
                </div>

                <!-- Bottom Section -->
                <div class="pt-4 border-t border-slate-700">
                    <div class="flex flex-col md:flex-row justify-between items-center">
                        <div class="text-slate-400 text-sm mb-2 md:mb-0">
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
