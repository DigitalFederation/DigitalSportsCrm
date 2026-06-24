<div class="card w-full md:w-1/3">
    <div class="flex gap-x-2 items-center border-b border-gray-300 pb-2 mb-4">
        <x-svg.person-badge class="w-6 h-6 text-slate-600" />
        <span class="font-bold">{{ __('events.organizing_entity') }}</span>
    </div>
    <div class="flex flex-col gap-3">

        {{-- Entity Name --}}
        @if($event->organizer?->organizable)
            <div>
                <p class="text-xs text-slate-400">{{ __('events.entity_name') }}</p>
                <p class="text-base text-slate-700 font-medium">
                    {{ $event->organizer->organizable->name }}
                </p>
            </div>
        @endif

        {{-- Email --}}
        @if($event->organizerDetails?->email_contact)
            <div>
                <p class="text-xs text-slate-400">{{ __('common.email') }}</p>
                <a href="mailto:{{ $event->organizerDetails->email_contact }}"
                   class="text-base text-slate-600 flex items-center gap-x-2 hover:text-indigo-600">
                    <x-svg.email class="w-4 h-4 text-slate-400" />
                    {{ $event->organizerDetails->email_contact }}
                </a>
            </div>
        @endif

        {{-- Phone --}}
        @if($event->organizerDetails?->phone_contact)
            <div>
                <p class="text-xs text-slate-400">{{ __('common.phone') }}</p>
                <a href="tel:{{ $event->organizerDetails->phone_contact }}"
                   class="text-base text-slate-600 flex items-center gap-x-2 hover:text-indigo-600">
                    <x-svg.phone class="w-4 h-4 text-slate-400" />
                    {{ $event->organizerDetails->phone_contact }}
                </a>
            </div>
        @endif

        {{-- Fallback if no organizer info --}}
        @if(!$event->organizer?->organizable && !$event->organizerDetails?->email_contact && !$event->organizerDetails?->phone_contact)
            <x-utility.no-data :inCard="true" />
        @endif

    </div>
</div>
