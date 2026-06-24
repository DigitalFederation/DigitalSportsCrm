<x-layout>
    <div class="previous-layout-classes">
        <div class="sm:flex sm:justify-between sm:items-center mb-4">
            <div>
                <h1 class="page-first-title">{{ __('sports.create_sport') }}</h1>
            </div>
        </div>

        <form action="{{ route('admin.evt-events.sport.store') }}" method="POST">
            @csrf
            @include('web.admin.evt_events.sport.form', ['sport' => new \Domain\EvtEvents\Models\Sport()])
        </form>
    </div>
</x-layout>
