<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-end sm:items-center mb-4">

            <!-- Actions -->
            @can('manage-events')
                @if(isset($isDefaultFederation) && $isDefaultFederation)
                    <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2 items-center">
                        <a class="btn btn-primary" href="{{ route('federation.evt-events.events.create', 'competition') }}">
                            {{ __('events.competition_event') }}
                        </a>

                        <a class="btn btn-primary" href="{{ route('federation.evt-events.events.create', 'organization') }}">
                            {{ __('events.organization_event') }}
                        </a>
                    </div>
                @endif
            @endcan

        </div>

        <div class="sm:flex sm:justify-center sm:items-center mb-5">
            @livewire('federation.evt-events.events-table')
        </div>

    </div>
</x-layout>
