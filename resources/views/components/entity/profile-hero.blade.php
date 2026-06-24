@props(['entity'])

<div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
    <div class="px-4 sm:px-6 py-4 sm:py-6">
        <div class="flex flex-col md:flex-row gap-4 sm:gap-6 md:gap-8 items-start md:items-center">
            <!-- Left: Logo and QR -->
            <div class="flex gap-4 sm:gap-6 items-center">
                <!-- Entity Logo -->
                <div class="relative">
                    <img class="w-20 h-20 sm:w-24 sm:h-24 md:w-32 md:h-32 rounded-xl object-cover ring-4 ring-white shadow-lg"
                         src="{{ $entity->getFirstMediaUrl('profile', 'thumb') ?: asset('img/user_placeholder.png') }}"
                         alt="{{ $entity->name }}">
                    <!-- Country Flag Badge -->
                    <div class="absolute -bottom-2 -right-2 bg-white rounded-full p-1 shadow-lg">
                        <img class="w-6 h-6 md:w-8 md:h-8 rounded-full"
                             src="{{ asset('img/flags/' . strtolower($entity->country->iso) . '.svg') }}"
                             alt="{{ $entity->country->name }}">
                    </div>
                </div>

                <!-- QR Code (hidden on mobile) -->
                @if(!empty($entity->qrcode_path))
                <div class="hidden md:block bg-gray-50 p-2 rounded-lg border border-gray-200">
                    <img src="{{ $entity->qrcode_path }}"
                         alt="{{ $entity->member_code }}"
                         class="w-20 h-20">
                </div>
                @endif
            </div>

            <!-- Center: Entity Info -->
            <div class="flex-1">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                    <div>
                        <!-- Name -->
                        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900">
                            {{ $entity->name }}
                        </h1>
                        @if($entity->legal_name && $entity->legal_name !== $entity->name)
                            <p class="text-sm text-gray-500 mt-0.5">{{ $entity->legal_name }}</p>
                        @endif
                        <p class="text-sm sm:text-base text-gray-500 mt-1">{{ __('entity.entity_detail') }} {{ __('main.Active since') }} {{ $entity->created_at->format('Y') }}</p>

                        <!-- Key Info Row -->
                        <div class="flex flex-wrap gap-3 sm:gap-4 md:gap-6 mt-3 sm:mt-4">
                            <!-- Member Code -->
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('main.Member Code') }}</p>
                                <p class="text-base sm:text-lg font-bold text-primary">{{ $entity->member_code }}</p>
                            </div>

                            <!-- Member Number -->
                            @if($entity->member_number)
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('main.member_number') }}</p>
                                <p class="text-base sm:text-lg font-semibold text-gray-900">{{ $entity->member_number }}</p>
                            </div>
                            @endif

                            <!-- Country -->
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('entity.country') }}</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <img class="w-5 h-5 rounded-full"
                                         src="{{ asset('img/flags/' . strtolower($entity->country->iso) . '.svg') }}"
                                         alt="{{ $entity->country->name }}">
                                    <p class="text-base sm:text-lg font-semibold text-gray-900">{{ $entity->country->name }}</p>
                                </div>
                            </div>

                            <!-- Location -->
                            @if($entity->location)
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('entity.location') }}</p>
                                <p class="text-base sm:text-lg font-semibold text-gray-900">{{ $entity->location }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Right: Actions -->
                    <div class="flex flex-col items-start md:items-end gap-3">
                        <!-- Entity ID -->
                        <p class="text-xs text-gray-400">ID: {{ substr($entity->id, 0, 8) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
