@props(['individual', 'individualType' => 'individual', 'editable' => false])

<div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
    <div class="px-4 sm:px-6 py-4 sm:py-6">
        <div class="flex flex-col md:flex-row gap-4 sm:gap-6 md:gap-8 items-start md:items-center">
            <!-- Left: Photo and QR -->
            <div class="flex gap-4 sm:gap-6 items-center">
                <!-- Profile Photo -->
                <div class="relative group">
                    <x-secure-profile-image :individual="$individual" size="thumb" class="w-20 h-20 sm:w-24 sm:h-24 md:w-32 md:h-32 rounded-xl object-cover ring-4 ring-white shadow-lg" />
                    @if($editable)
                        <button type="button"
                                onclick="window.Livewire.dispatch('toggle-member-photo-editor')"
                                class="absolute inset-0 flex items-center justify-center bg-black/40 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer"
                                title="{{ __('profile.edit_photo') }}">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </button>
                    @endif
                    <!-- Country Flag Badge -->
                    <div class="absolute -bottom-2 -right-2 bg-white rounded-full p-1 shadow-lg">
                        <img class="w-6 h-6 md:w-8 md:h-8 rounded-full"
                             src="{{ asset('img/flags/' . strtolower($individual->country->iso) . '.svg') }}"
                             alt="{{ $individual->country->name }}">
                    </div>
                </div>

                <!-- QR Code (hidden on mobile) -->
                @if(!empty($individualType) && in_array($individualType, ['individual', 'assistant', 'instructor']))
                <div class="hidden md:block bg-gray-50 p-2 rounded-lg border border-gray-200">
                    <img src="{{ $individual->qrcode_path }}" 
                         alt="{{ $individual->member_code }}"
                         class="w-20 h-20">
                </div>
                @endif
            </div>

            <!-- Center: Personal Info -->
            <div class="flex-1">
                <!-- Name and Title -->
                <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900">
                    {{ $individual->native_name ?? $individual->full_name }}
                </h1>

                <!-- Key Info Row -->
                <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-3 sm:gap-4 md:gap-6 mt-3 sm:mt-4">
                    <!-- Nº Filiado -->
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('main.Member Code') }}</p>
                        <p class="text-sm sm:text-base md:text-lg font-bold text-primary truncate">{{ $individual->member_code }}</p>
                    </div>

                    <!-- Member Number -->
                    @if($individual->member_number)
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('main.member_number') }}</p>
                        <p class="text-sm sm:text-base md:text-lg font-semibold text-gray-900 truncate">{{ $individual->member_number }}</p>
                    </div>
                    @endif

                    <!-- Nationality -->
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('main.Nationality') }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <img class="w-5 h-5 rounded-full flex-shrink-0"
                                 src="{{ asset('img/flags/' . strtolower($individual->country->iso) . '.svg') }}"
                                 alt="{{ $individual->country->name }}">
                            <p class="text-sm sm:text-base md:text-lg font-semibold text-gray-900 truncate">{{ $individual->country->name }}</p>
                        </div>
                    </div>

                    <!-- Birthday -->
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('main.Birthday') }}</p>
                        <p class="text-sm sm:text-base md:text-lg font-semibold text-gray-900">
                            {{ $individual->birthdate ? Carbon\Carbon::parse($individual->birthdate)->format('d/m/Y') : '---' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>