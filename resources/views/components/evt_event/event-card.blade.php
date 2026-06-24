@php
    $segment = \Illuminate\Support\Facades\Request::segment(1);
    $detailUrl = in_array($segment, ['admin', 'federation', 'entity', 'individual', 'cmas'], true)
        ? route("$segment.evt-events.events.show", $event->id)
        : route('public.event.show', $event);
@endphp
<div class="flex flex-col md:flex-row h-full hover:shadow-xl">
    <div
        class="md:w-1/3 w-full">
        <!-- Placeholder image -->
        <a href="{{ $detailUrl }}">
            @if($event->featured_image)
                <img class="h-full w-full object-cover rounded-t md:rounded-l md:rounded-r-none"
                     src="{{ asset('storage/' . $event->featured_image) }}"
                     alt="">
            @else
                @if($event->event_category == 'competition')
                    <img class="h-full w-full object-cover rounded-t md:rounded-l md:rounded-r-none"
                         src="{{ asset('img/placeholder_event_competition.png') }}"
                         alt="CMAS Event">
                @else
                    <img class="h-full w-full object-cover rounded-t md:rounded-l md:rounded-r-none"
                         src="{{ asset('img/placeholder_event_organization.png') }}"
                         alt="CMAS Event">
                @endif

            @endif
        </a>
    </div>

    <div
        class="md:w-2/3 w-full bg-white shadow-sm rounded-b md:rounded-r border border-slate-100 relative p-4 flex flex-col justify-between">
        <div>
            <span class="text-lg text-blue-600">
                {{ isset($event->organization_type) ? \App\Enums\EvtEventOrganizationCategoryEnum::toString($event->organization_type) : __('events.competition') }}
            </span>
        </div>
        <div class="mt-2">
            <a class="text-slate-700 text-xl font-bold hover:text-slate-600 rounded-full"
               href="{{ $detailUrl }}">
                {{ $event->name }}
            </a>
        </div>
        <div class="mb-4">
            <span class="text-sm text-slate-400">
                {{ \App\Enums\EvtEventEnrollmentTypeEnum::toString($event->enrollment_type) }}
            </span>
            <div class="text-sm text-slate-400">

                @if(!empty($event->start_date))
                    <span class="font-bold">{{ __('events.start_date') }}:</span>
                    <span>{{ date('d/m/Y', strtotime($event->start_date)) }}</span>
                @elseif(!empty(optional($event->competition)->start_date))
                    <span class="font-bold">{{ __('events.start_date') }}:</span>
                    <span>{{ date('d/m/Y', strtotime($event->competition->start_date))  }}</span>
                @endif

            </div>
        </div>
        <div class="mt-auto">
            <!-- Status class -->
            <x-tables.badge :status="ucfirst($event->stateName())"
                            :color="$event->stateColor()" />
        </div>
    </div>
</div>
