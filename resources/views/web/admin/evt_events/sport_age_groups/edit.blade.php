<x-layout>
    <div class="previous-layout-classes">
        <div class="sm:flex sm:justify-between sm:items-center mb-4">
            <div>
                <h1 class="page-first-title">{{ __('Edit Sport Age Group') }}</h1>
            </div>
        </div>

        <form action="{{ route('admin.evt-events.sport-age-groups.update', ['sport_age_group' => $sportAgeGroup->id]) }}"
              method="POST">
            @csrf
            @method('PUT')
            @include('web.admin.evt_events.sport_age_groups.form', ['ageGroup' => $sportAgeGroup])
        </form>
    </div>
</x-layout>
