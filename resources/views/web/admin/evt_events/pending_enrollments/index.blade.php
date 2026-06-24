<x-layout>
    <div class="previous-layout-classes">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title text-2xl">{{ __('Waiting list for') }} {{ $event->name }}</h1>
            </div>
            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2"></div>
        </div>

        <!-- Waiting Lists -->
        <div class="mb-8">
            @foreach($waitingLists as $federationId => $waitingList)
                <div class="mb-4 card">
                    <h2 class="text-xl font-semibold mb-2">{{ $waitingList['federation_name'] }}</h2>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <h3 class="text-sm font-semibold mb-2">{{ __('Information') }}</h3>
                            <p>{{ __('Number of pending enrollments:') }} {{ $waitingList['count'] }}</p>
                            <p>{{ __('Date:') }} {{ date('d/m/Y', strtotime($waitingList['enrollment_date'])) }} </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold mb-2">{{ __('Enrollment Breakdown') }}</h3>
                            <ul>
                                <li>{{ __('Individuals:') }} {{ $waitingList['individual_count'] }}</li>
                                <li>{{ __('Athletes:') }} {{ $waitingList['athlete_count'] }}</li>
                                <li>{{ __('Coaches:') }} {{ $waitingList['coach_count'] }}</li>
                                <li>{{ __('Referees:') }} {{ $waitingList['referee_count'] }}</li>
                                <li>{{ __('Team Officials:') }} {{ $waitingList['team_official_count'] }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>


    </div>
</x-layout>
