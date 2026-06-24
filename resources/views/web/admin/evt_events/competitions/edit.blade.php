@section('title', __('Competition Details'))
<x-layout>
    <div class="previous-layout-classes">

        <div class="mb-4">
            <h1 class="page-first-title"> {{ __('Competition Details') }}  </h1>
            <p>
                <a class="hover:underline"
                   href="{{ route('admin.evt-events.events.show', $event->id) }}"> {{ __('Event') }}</a>
                &raquo; {{ __('Competition detail') }}
            </p>
        </div>

        <livewire:event-competition-form :competition="$competition" :event="$event" />
    </div>

</x-layout>
