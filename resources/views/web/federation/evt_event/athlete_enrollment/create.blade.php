@section('title', __('Athletes Enrollment'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        @php
            $actions = [
                [
                    'type' => 'link',
                    'class' => 'btn-sm btn-info',
                    'url' => $discipline->exists ? route('federation.evt-events.events.disciplines.athlete-enrollment.index', ['event' => $event, 'discipline' => $discipline]) : route('federation.evt-events.events.athlete-enrollment.index', $event->id),
                    'text' => __('Back'),
                ],
            ];
        @endphp
        <x-layout.page-header
            :title="__('Athletes Enrollment')"
            :subtitle="$event->name"
            :actions="$actions"
        ></x-layout.page-header>

        <livewire:federation-create-athlete-enrollment
            :event="$event"
            :federation="$federation"
        />

    </div>

</x-layout>
