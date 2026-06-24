{{-- public/location-map/_modal.blade.php --}}
<div x-data="{ open: @entangle('selectedItem').live }"
x-show="open"
x-transition:enter="transition ease-out duration-300"
x-transition:enter-start="opacity-0"
x-transition:enter-end="opacity-100"
x-transition:leave="transition ease-in duration-200"
x-transition:leave-start="opacity-100"
x-transition:leave-end="opacity-0"
class="fixed inset-0 bg-black/30 backdrop-blur-sm z-20 flex items-start justify-center overflow-y-auto"
style="display: none;">

<div x-show="open"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 transform scale-95"
    x-transition:enter-end="opacity-100 transform scale-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 transform scale-100"
    x-transition:leave-end="opacity-0 transform scale-95"
    @click.outside="$wire.closeModal()"
    class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 my-8 overflow-hidden border border-gray-100">

    @if($this->selectedItemDetails)
    <div class="relative">
        {{-- Header with background --}}
        <div class="relative h-32 bg-gradient-to-r from-blue-600 to-blue-800">
            <div class="absolute inset-0 bg-black/20"></div>
            <div class="absolute bottom-0 left-0 right-0 p-6">
                <div class="flex justify-between items-end">
                    <div>
                        <div class="text-blue-200 text-sm mb-1">
                            {{ $this->selectedItem['type'] === 'federation' ? __('location-map.Federation') : __('location-map.Entity') }}
                        </div>
                        <h3 class="text-2xl font-bold text-white">
                            {{ $this->selectedItemDetails->name }}
                        </h3>
                    </div>
                    <button @click="$wire.closeModal()"
                            class="rounded-full p-2 bg-white/10 hover:bg-white/20 transition-colors">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div class="p-6">
            {{-- Location Info --}}

            <div class="flex flex-row gap-x-4 justify-between">
                <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-xl mb-6">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex-grow">
                        <h4 class="text-sm font-bold text-gray-900 mb-1">{{ __('location-map.Location details') }}</h4>
                        <div class="text-sm text-gray-600">
                            {{ $this->selectedItemDetails->address }}<br>
                            {{ $this->selectedItemDetails->location }}<br>
                            {{ $this->selectedItemDetails->country?->name }}
                        </div>
                    </div>
                </div>

                <div>
                    @if($this->selectedItemDetails->logo_url)
                        <img src="{{ $this->selectedItemDetails->logo_url }}" alt="Logo"
                             class=" w-28 h-28 object-cover rounded-full">
                    @else
                        <div class=" w-32 h-32 md:w-40 md:h-40 flex items-center justify-center bg-slate-300 rounded-lg">
                            <svg class="h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Contact Info --}}
            @if($this->selectedItemDetails->email || $this->selectedItemDetails->phone || $this->selectedItemDetails->website)
            <div class="mb-6">
                <div class="p-4 bg-white border border-gray-100 rounded-xl">
                    <div class="space-y-4">
                        @if($this->selectedItemDetails->email)
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="min-w-0"> {{-- Add min-w-0 to allow text truncation --}}
                                <div class="text-xs text-gray-500 mb-1">{{ __('location-map.Email') }}</div>
                                <a href="mailto:{{ $this->selectedItemDetails->email }}"
                                class="text-sm font-medium text-gray-900 hover:text-blue-600 break-all">
                                    {{ $this->selectedItemDetails->email }}
                                </a>
                            </div>
                        </div>
                        @endif

                        @if($this->selectedItemDetails->phone)
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-10 h-10 bg-emerald-50 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 mb-1">{{ __('location-map.Phone') }}</div>
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $this->selectedItemDetails->phone }}
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($this->selectedItemDetails->website)
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-10 h-10 bg-sky-50 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656-5.656l-4-4a4 4 0 00-5.656 5.656l1.101 1.102" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 mb-1">{{ __('location-map.Website') }}</div>
                                @php
                                    $websiteUrl = $this->selectedItemDetails->website;
                                    if (!preg_match("~^(?:f|ht)tps?://~i", $websiteUrl)) {
                                        $websiteUrl = "https://" . $websiteUrl;
                                    }
                                @endphp
                                <a href="{{ $websiteUrl }}" target="_blank" rel="noopener noreferrer"
                                   class="text-sm font-medium text-gray-900 hover:text-sky-600 break-all">
                                    {{ $this->selectedItemDetails->website }}
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Specific Details Section --}}
            @if($this->selectedItem['type'] === 'federation')
                {{-- Federation-specific content --}}
                <div class="space-y-6">

                    {{-- Memberships Info --}}

                    <div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
                        <div class="px-4 py-3 bg-gray-50 border-b border-gray-100">
                            <h4 class="font-medium text-gray-900 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z" clip-rule="evenodd" />
                                </svg>
                                {{ __('location-map.Committees and Comissions') }}
                            </h4>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-2 gap-4">
                                @php
                                $committees = collect($this->selectedItemDetails->memberships)
                                    ->filter()
                                    ->flatMap(function($membership) {
                                        return $membership->plans ?? collect();
                                    })
                                    ->filter(function($plan) {
                                        return $plan && $plan->relationLoaded('committee') && $plan->committee;
                                    })
                                    ->map(function($plan) {
                                        return $plan->committee;
                                    })
                                    ->unique('id')
                                    ->values();
                            @endphp
                                @forelse($committees as $committee)
                                    <div class="p-3 bg-white border border-gray-100 rounded-lg">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $committee->name }}
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-span-2 p-4 text-center text-gray-500">
                                        {{ __('location-map.No committees found') }}
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            @else
                {{-- Entity-specific content --}}
                <div class="space-y-6">
                    {{-- Licenses Section --}}
                    <div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
                        <div class="px-4 py-3 bg-gray-50 border-b border-gray-100">
                            <h4 class="font-medium text-gray-900 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                {{ __('location-map.Active Licenses') }}
                            </h4>
                        </div>
                        <div class="p-4">
                            <div class="space-y-3">
                                @forelse($this->selectedItemDetails->licenses as $license)
                                    <div class="flex items-center gap-3 p-3 bg-blue-50 rounded-lg">
                                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                            </svg>
                                        </div>
                                        <div class="flex-grow">
                                            <div class="text-sm font-medium text-blue-900">{{ $license->license->name }}</div>
                                            <div class="text-xs text-blue-700">
                                                {{ $license->license->committee ? __('location-map.committee_' . $license->license->committee->code) : '' }}
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-4">
                                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 mb-3">
                                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                        </div>
                                        <p class="text-sm text-gray-500">{{ __('location-map.No active licenses') }}</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Federation Affiliations --}}
                    <div class="bg-white border border-gray-100 rounded-xl overflow-hidden">
                        <div class="px-4 py-3 bg-gray-50 border-b border-gray-100">
                            <h4 class="font-medium text-gray-900 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                                </svg>
                                {{ __('location-map.National Federations & Organizations') }}
                            </h4>
                        </div>
                        <div class="p-4">
                            <div class="space-y-2">
                                @foreach($this->selectedItemDetails->federations as $federation)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                                <span class="text-sm font-medium text-blue-700">
                                                    {{ substr($federation->name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $federation->name }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $federation->country?->name }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- View Entity Button --}}
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <a href="{{ route('public.entity.show', ['entity' => $this->selectedItemDetails->id]) }}"
                           class="btn block w-full text-center px-6 py-3 border border-transparent rounded-xl shadow-sm text-base font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            {{ __('location-map.View Entity Information') }}
                        </a>
                    </div>
                </div>
            @endif
        </div>

        {{-- Footer with actions --}}
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    @if($this->selectedItemDetails->member_code)
                        <span class="flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.083 9h1.946c.089-1.546.383-2.97.837-4.118A6.004 6.004 0 004.083 9zM10 2a8 8 0 100 16 8 8 0 000-16zm0 2c-.076 0-.232.032-.465.262-.238.234-.497.623-.737 1.182-.389.907-.673 2.142-.766 3.556h3.936c-.093-1.414-.377-2.649-.766-3.556-.24-.56-.5-.948-.737-1.182C10.232 4.032 10.076 4 10 4zm3.971 5c-.089-1.546-.383-2.97-.837-4.118A6.004 6.004 0 0115.917 9h-1.946zm-2.003 2H8.032c.093 1.414.377 2.649.766 3.556.24.56.5.948.737 1.182.233.23.389.262.465.262.076 0 .232-.032.465-.262.238-.234.498-.623.737-1.182.389-.907.673-2.142.766-3.556zm1.166 4.118c.454-1.147.748-2.572.837-4.118h1.946a6.004 6.004 0 01-2.783 4.118zm-6.268 0C6.412 13.97 6.118 12.546 6.03 11H4.083a6.004 6.004 0 002.783 4.118z" clip-rule="evenodd" />
                            </svg>
                            {{ $this->selectedItemDetails->member_code }}
                        </span>
                    @endif
                </div>
                <div class="flex gap-3">
                    @if($this->selectedItemDetails->website)
                        @php
                            $websiteUrlFooter = $this->selectedItemDetails->website;
                            if (!preg_match("~^(?:f|ht)tps?://~i", $websiteUrlFooter)) {
                                $websiteUrlFooter = "https://" . $websiteUrlFooter;
                            }
                        @endphp
                        <a href="{{ $websiteUrlFooter }}"
                        target="_blank" rel="noopener noreferrer"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                            {{ __('location-map.Visit Website') }}
                        </a>
                    @endif
                    <button @click="$wire.closeModal()"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        {{ __('location-map.Close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
</div>
