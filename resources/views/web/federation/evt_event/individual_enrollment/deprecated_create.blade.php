@section('title', 'Member Enrollment')
<x-layout>
    <div class="previous-layout-classes">
        <!-- Page header -->
        @php
            $actions = [
                [
                    'type' => 'link',
                    'class' => 'btn-sm btn-info',
                    'url' => route('federation.evt-events.events.individual-enrollment.index', ['event' => $event]),
                    'text' => 'Back'
                ]
            ];
        @endphp


        <x-layout.page-header
            title="Member Enrollment"
            :subtitle="$event->name"
            :actions="$actions"
        ></x-layout.page-header>

       
        <livewire:federation-create-individual-enrollment :event="$event" :federation="$federation->id" />

    </div>

</x-layout>
