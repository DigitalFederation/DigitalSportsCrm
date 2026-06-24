@php
    $organizerEntity = $event->organizer?->organizable;
    $isEntity = $organizerEntity instanceof \Domain\Entities\Models\Entity;
@endphp

<div class="card h-full">
    <div class="flex gap-x-2 items-center border-b border-gray-200 pb-3 mb-4">
        <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        <span class="font-semibold text-slate-700">{{ __('events.organizing_entity') }}</span>
    </div>

    @if($isEntity && $organizerEntity)
        <div class="flex flex-col gap-4">
            {{-- 1. Foto da Entidade --}}
            <div class="flex items-center gap-4">
                @php
                    $entityPhoto = $organizerEntity->getFirstMediaUrl('profile', 'thumb') ?: $organizerEntity->getFirstMediaUrl('profile');
                @endphp
                @if($entityPhoto)
                    <img src="{{ $entityPhoto }}" alt="{{ $organizerEntity->name }}"
                         class="w-16 h-16 rounded-lg object-cover border border-slate-200 shadow-sm">
                @else
                    <div class="w-16 h-16 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                @endif

                {{-- 2. Nome da Entidade --}}
                <div class="flex-1">
                    <p class="text-xs text-slate-500 uppercase tracking-wide">{{ __('events.entity_name') }}</p>
                    <p class="text-base font-semibold text-slate-700">{{ $organizerEntity->name }}</p>
                </div>
            </div>

            {{-- 3. Distrito --}}
            @if($organizerEntity->district)
                <div class="bg-slate-50 rounded-lg p-3">
                    <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">{{ __('events.form.venue_district') }}</p>
                    <p class="text-sm font-medium text-slate-700 flex items-center gap-2">
                        <x-heroicon-o-map-pin class="w-4 h-4 text-slate-400" />
                        {{ $organizerEntity->district->name }}
                    </p>
                </div>
            @endif

            {{-- 4. Email --}}
            @if($organizerEntity->email)
                <div class="bg-slate-50 rounded-lg p-3">
                    <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">{{ __('common.email') }}</p>
                    <a href="mailto:{{ $organizerEntity->email }}"
                       class="text-sm font-medium text-indigo-600 hover:text-indigo-800 flex items-center gap-2">
                        <x-heroicon-o-envelope class="w-4 h-4" />
                        {{ $organizerEntity->email }}
                    </a>
                </div>
            @endif

            {{-- 5. Telefone --}}
            @if($organizerEntity->phone)
                <div class="bg-slate-50 rounded-lg p-3">
                    <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">{{ __('common.phone') }}</p>
                    <a href="tel:{{ $organizerEntity->phone }}"
                       class="text-sm font-medium text-slate-700 hover:text-indigo-600 flex items-center gap-2">
                        <x-heroicon-o-phone class="w-4 h-4 text-slate-400" />
                        {{ $organizerEntity->phone }}
                    </a>
                </div>
            @endif
        </div>
    @elseif($event->organizer)
        {{-- Fallback para organizadores que não são Entity (ex: Federation) --}}
        @php
            $organizable = $event->organizer->organizable;
            $email = $event->organizerDetails?->email_contact ?? $organizable?->email;
            $phone = $event->organizerDetails?->phone_contact ?? $organizable?->phone;
            $address = $organizable?->address;
            $location = $organizable?->location;
            $zipCode = $organizable?->zip_code;
            $website = $organizable?->website;
        @endphp
        <div class="flex flex-col gap-3">
            <div class="bg-slate-50 rounded-lg p-3">
                <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">{{ __('events.form.organizer') }}</p>
                <p class="text-sm font-medium text-slate-700">{{ $organizable?->name }}</p>
            </div>

            @if($email)
                <div class="bg-slate-50 rounded-lg p-3">
                    <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">{{ __('common.email') }}</p>
                    <a href="mailto:{{ $email }}"
                       class="text-sm font-medium text-indigo-600 hover:text-indigo-800 flex items-center gap-2">
                        <x-heroicon-o-envelope class="w-4 h-4" />
                        {{ $email }}
                    </a>
                </div>
            @endif

            @if($phone)
                <div class="bg-slate-50 rounded-lg p-3">
                    <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">{{ __('common.phone') }}</p>
                    <a href="tel:{{ $phone }}"
                       class="text-sm font-medium text-slate-700 hover:text-indigo-600 flex items-center gap-2">
                        <x-heroicon-o-phone class="w-4 h-4 text-slate-400" />
                        {{ $phone }}
                    </a>
                </div>
            @endif

            @if($address || $location)
                <div class="bg-slate-50 rounded-lg p-3">
                    <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">{{ __('main.address') }}</p>
                    <p class="text-sm font-medium text-slate-700 flex items-center gap-2">
                        <x-heroicon-o-map-pin class="w-4 h-4 text-slate-400" />
                        {{ collect([$address, $zipCode, $location])->filter()->implode(', ') }}
                    </p>
                </div>
            @endif

            @if($website)
                <div class="bg-slate-50 rounded-lg p-3">
                    <p class="text-xs text-slate-500 uppercase tracking-wide mb-1">{{ __('events.website') }}</p>
                    <a href="{{ $website }}" target="_blank" rel="noopener noreferrer"
                       class="text-sm font-medium text-indigo-600 hover:text-indigo-800 flex items-center gap-2">
                        <x-heroicon-o-globe-alt class="w-4 h-4" />
                        {{ $website }}
                    </a>
                </div>
            @endif
        </div>
    @else
        <div class="text-center py-6 text-slate-400">
            <svg class="w-12 h-12 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <p class="text-sm">{{ __('events.no_organizer_assigned') }}</p>
        </div>
    @endif
</div>
