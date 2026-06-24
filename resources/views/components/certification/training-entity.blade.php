{{-- resources/views/components/certification/training-entity.blade.php --}}
@if($hasEntity)
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="flex flex-col md:flex-row">
            {{-- Main Content Section --}}
            <div class="flex-1">
                {{-- Header --}}


                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-start space-x-4">
                        {{-- Entity Logo/Image --}}
                        <div class="relative flex-shrink-0">
                            <div class="h-24 w-24 rounded-lg overflow-hidden bg-gray-100 border border-gray-200">
                                @if($hasLogo)
                                    <img
                                        src="{{ $entityMedia->getUrl('thumb') }}"
                                        alt="{{ $entity->name }} logo"
                                        class="h-full w-full object-cover"
                                    >
                                @else
                                    <div class="h-full w-full flex items-center justify-center bg-gray-50">
                                        <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24"
                                             stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    </div>
                                @endif

                                {{-- QR Code Overlay --}}
                                @if($hasQrCode)
                                    <div
                                        class="absolute -bottom-2 -right-2 h-10 w-10 bg-white rounded-lg shadow-lg p-1">
                                        <img
                                            src="{{ $entity->qrcode_path }}"
                                            alt="Entity QR Code"
                                            class="h-full w-full"
                                        >
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Entity Title and Basic Info --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-bold text-gray-900 truncate">
                                    {{ __('certifications.training_entity.school') }}
                                </h2>
                                @if($entity->member_code)
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ __('main.Member Code') }}: {{ $entity->member_code }}
                                </span>
                                @endif
                            </div>
                            <div class="mt-1">
                                @if(auth()->user()->group->code != 'ENTITY')
                                    <a href="{{ route(strtolower(auth()->user()->group->code).'.entity.show', $entity->id) }}"
                                       class="text-xl font-medium text-gray-900 hover:text-blue-600 hover:underline">
                                        {{ ucwords(strtolower($entity->name)) }}
                                    </a>
                                @else
                                    <a href="#"
                                       class="text-xl font-medium text-gray-900 hover:text-blue-600 hover:underline">
                                        {{ ucwords(strtolower($entity->name)) }}
                                    </a>
                                @endif
                            </div>
                            @if($entity->legal_name)
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $entity->legal_name }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Rest of the existing content --}}
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                        {{-- Location Information --}}
                        <div class="space-y-4">
                            @if(!empty($locationInfo))
                                <div class="space-y-3">
                                    @if(!empty($locationInfo['address']))
                                        <div>
                                            <div class="text-sm font-medium text-gray-500">{{ __('certifications.training_entity.address') }}</div>
                                            <p class="mt-1 text-gray-900">{{ $locationInfo['address'] }}</p>
                                        </div>
                                    @endif

                                    @if(!empty($locationInfo['postal_code']) && !empty($locationInfo['location']))
                                        <div class="grid grid-cols-2 gap-4">

                                            <div>
                                                <div
                                                    class="text-sm font-medium text-gray-500">{{ __('certifications.training_entity.postal_code') }}</div>
                                                <p class="mt-1 text-gray-900">{{ $locationInfo['postal_code'] }}</p>
                                            </div>

                                            <div>
                                                <div
                                                    class="text-sm font-medium text-gray-500">{{ __('certifications.training_entity.location') }}</div>
                                                <p class="mt-1 text-gray-900">{{ $locationInfo['location'] }}</p>
                                            </div>

                                        </div>
                                    @endif

                                    @if(!empty($locationInfo['country']) && !empty($locationInfo['country_iso']))
                                        <div>
                                            <div class="text-sm font-medium text-gray-500">{{ __('certifications.training_entity.country') }}</div>
                                            <p class="mt-1 flex items-center space-x-2">
                                                <img
                                                    src="{{ asset('img/flags/' . $locationInfo['country_iso'] . '.svg') }}"
                                                    alt="{{ $locationInfo['country'] }} flag"
                                                    class="w-5 h-5 rounded-sm object-cover"
                                                />
                                                <span class="text-gray-900">{{ $locationInfo['country'] }}</span>
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- Contact Information --}}
                        <div class="space-y-4">
                            @if(!empty($contactInfo))
                                <div class="space-y-3">
                                    @if(!empty($contactInfo['phone']))
                                        <div>
                                            <div class="text-sm font-medium text-gray-500">{{ __('certifications.training_entity.phone') }}</div>
                                            <p class="mt-1">
                                                <a href="tel:{{ $contactInfo['phone'] }}"
                                                   class="text-gray-900 hover:text-blue-600">
                                                    {{ $contactInfo['phone'] }}
                                                </a>
                                            </p>
                                        </div>
                                    @endif

                                    @if(!empty($contactInfo['email']))
                                        <div>
                                            <div class="text-sm font-medium text-gray-500">{{ __('certifications.training_entity.email') }}</div>
                                            <p class="mt-1">
                                                <a href="mailto:{{ $contactInfo['email'] }}"
                                                   class="text-gray-900 hover:text-blue-600">
                                                    {{ $contactInfo['email'] }}
                                                </a>
                                            </p>
                                        </div>
                                    @endif

                                    @if(!empty($contactInfo['website']))
                                        <div>
                                            <div class="text-sm font-medium text-gray-500">{{ __('certifications.training_entity.website') }}</div>
                                            <p class="mt-1">
                                                <a href="{{ $contactInfo['website'] }}"
                                                   target="_blank"
                                                   rel="noopener"
                                                   class="text-gray-900 hover:text-blue-600">
                                                    {{ str_replace(['http://', 'https://'], '', $contactInfo['website']) }}
                                                </a>
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

            {{-- Map Section (Side Panel) --}}
            @if($entity->lat && $entity->lng)
                <div class="lg:w-1/3 xl:w-2/5 h-full border-l border-gray-200">
                    <div class="h-full min-h-[300px]">
                        <x-certification.school-map :entity="$entity" />
                    </div>
                </div>
            @endif
        </div>
    </div>
@endif
