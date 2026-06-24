@props(['entity'])

<section class="w-full bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-5">
        <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
            <!-- Entity Logo -->
            <div class="flex-shrink-0">
                <div class="w-20 h-20 md:w-24 md:h-24 rounded-xl overflow-hidden border-2 border-gray-100 shadow-sm bg-gray-50 flex items-center justify-center">
                    @if($entity->getFirstMediaUrl('profile', 'thumb'))
                        <img class="object-cover w-full h-full"
                            src="{{ $entity->getFirstMediaUrl('profile', 'thumb') }}"
                            alt="{{ $entity->name }}">
                    @else
                        <svg class="w-12 h-12 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                        </svg>
                    @endif
                </div>
            </div>

            <!-- Entity Info -->
            <div class="flex-1 min-w-0">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 class="text-xl md:text-2xl font-bold text-gray-900 truncate">
                            {{ $entity->name }}
                        </h1>
                        <p class="mt-1 text-sm text-gray-500">{{ __('entity.dashboard.entity_profile') }}</p>
                    </div>

                    <!-- Key Info Badges -->
                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Member Number -->
                        @if($entity->member_number)
                        <div class="inline-flex items-center gap-2 px-3 py-2 bg-primary/5 rounded-lg border border-primary/10">
                            <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('entity.member_number') }}</span>
                            <span class="text-sm font-bold text-primary">{{ $entity->member_number }}</span>
                        </div>
                        @endif

                        <!-- Member Code / ID Number -->
                        <div class="inline-flex items-center gap-2 px-3 py-2 bg-gray-50 rounded-lg border border-gray-100">
                            <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('main.Member Code') }}</span>
                            <span class="text-sm font-bold text-gray-900">{{ $entity->member_code }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- QR Code -->
            @if(!empty($entity->qrcode_path))
            <div class="flex-shrink-0 hidden lg:block">
                <div class="p-2 bg-white rounded-lg border border-gray-200 shadow-sm">
                    <img src="{{ $entity->qrcode_path }}"
                         alt="{{ $entity->member_code }}"
                         class="w-16 h-16 md:w-20 md:h-20 object-contain">
                </div>
            </div>
            @endif
        </div>
    </div>
</section>
