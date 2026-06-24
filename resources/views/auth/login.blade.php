@section('title', __('Login'))
<x-guest-layout>
    @php($brand = config('branding.primary'))

    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-b from-gray-50 to-white">
        <!-- Premium top border accent -->
        <div class="fixed top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary via-primary-light to-secondary z-50"></div>
        
        <!-- Logo with enhanced styling -->
        <div class="flex flex-col items-center mt-6">
            <a href="/" class="transition-transform hover:scale-105 duration-300">
                <img src="{{ asset($brand['logo_path']) }}" class="h-20 sm:h-24" alt="{{ $brand['short_name'] }} logo">
            </a>
        </div>

        <!-- Banner messages -->        
        <div class="w-full sm:max-w-md px-4 sm:px-0">
            @include('components.layout.banner_message')
        </div>

        <!-- Main login card with premium styling -->        
        <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-white shadow-xl rounded-xl overflow-hidden border border-gray-100">
            <h2 class="text-center mb-6 font-bold text-xl text-gray-800">{{ __('Welcome Back') }}</h2>
            
            <x-validation-errors class="mb-4" />

            @if (session('status'))
                <div class="mb-4 p-3 bg-green-50 text-green-700 rounded-lg font-medium text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div>
                    <x-label for="email" value="{{ __('Email Address') }}" class="text-gray-700 font-medium" />
                    <x-input
                        id="email"
                        class="block mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary/20 transition-all duration-200"
                        type="email"
                        name="email"
                        :value="old('email')"
                        placeholder="your.email@example.com"
                        required autofocus />
                </div>

                <div class="mt-5">
                    <div class="flex justify-between items-center">
                        <x-label for="password" value="{{ __('Password') }}" class="text-gray-700 font-medium" />
                        @if (Route::has('password.request'))
                            <a class="text-xs text-primary hover:text-primary-dark transition-colors duration-150"
                               href="{{ route('password.request') }}">
                                {{ __('Forgot password?') }}
                            </a>
                        @endif
                    </div>
                    <x-input
                        id="password"
                        class="block mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary/20 transition-all duration-200"
                        type="password"
                        name="password"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password" />
                </div>

                <div class="block mt-5">
                    <label for="remember_me" class="flex items-center">
                        <x-checkbox id="remember_me" name="remember" class="text-primary focus:ring-primary/20" />
                        <span class="ml-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                    </label>
                </div>

                <div class="mt-6">
                    <button type="submit" class="w-full flex justify-center items-center py-2.5 px-4 bg-primary hover:bg-primary-dark text-white font-medium rounded-lg transition-colors duration-200 shadow-sm hover:shadow focus:outline-none focus:ring-2 focus:ring-primary/50 focus:ring-offset-2">
                        <span>{{ __('Sign In') }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </form>
        </div>

        <!-- Registration options with premium styling -->        
        <div class="w-full sm:max-w-md mt-8 px-4 sm:px-0">
            <h2 class="text-center mb-4 font-bold text-xl text-gray-800">{{ __('Need to register an account?') }}</h2>
            
            <div class="grid md:grid-cols-2 gap-4">
                <!-- Entity registration card -->                
                <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-300 overflow-hidden border border-gray-100">
                    <div class="p-5">
                        <div class="flex items-center justify-center w-12 h-12 mx-auto rounded-full bg-blue-50 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-center text-gray-800 mb-2">{{ __('Clubs/Schools/Centers') }}</h3>
                        <div class="text-center text-gray-500 text-xs space-y-1 mb-4">
                            <p>Sports Clubs</p>
                            <p>Diving School & Center</p>
                            <p>Scientific School & Center</p>
                        </div>
                    </div>
                    <div class="px-5 py-3 bg-gray-50 border-t border-gray-100">
                        <a href="/entity" class="block w-full py-2 bg-white hover:bg-gray-50 text-primary border border-primary font-medium rounded-lg text-center text-sm transition-colors duration-150">
                            {{ __('Create Entity Account') }}
                        </a>
                    </div>
                </div>
                
                <!-- Individual registration card -->                
                <div class="bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-300 overflow-hidden border border-gray-100">
                    <div class="p-5">
                        <div class="flex items-center justify-center w-12 h-12 mx-auto rounded-full bg-blue-50 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-center text-gray-800 mb-2">{{ __('Individual') }}</h3>
                        <div class="text-center text-gray-500 text-xs space-y-1 mb-4">
                            <p>Athletes, Coaches, Judges & Referees</p>
                            <p>Divers & Freedivers</p>
                            <p>Instructors</p>
                        </div>
                    </div>
                    <div class="px-5 py-3 bg-gray-50 border-t border-gray-100">
                        <a href="/individual" class="block w-full py-2 bg-white hover:bg-gray-50 text-primary border border-primary font-medium rounded-lg text-center text-sm transition-colors duration-150">
                            {{ __('Create Individual Account') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->        
        <div class="mt-8 mb-6 text-center text-xs text-gray-500">
            <p>© {{ date('Y') }} {{ $brand['portal_name'] }}. {{ __('All rights reserved.') }}</p>
        </div>
    </div>

</x-guest-layout>
