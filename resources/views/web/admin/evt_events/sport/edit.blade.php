<x-layout>
    <div class="previous-layout-classes">
        <div class="sm:flex sm:justify-between sm:items-center mb-4">
            <div>
                <h1 class="page-first-title">{{ __('sports.edit_sport') }}</h1>
            </div>
        </div>

        <form action="{{ route('admin.evt-events.sport.update', ['sport' => $sport->id]) }}"
              method="POST">
            @csrf
            @method('PUT')
            @include('web.admin.evt_events.sport.form', ['sport' => $sport])
        </form>
    </div>
</x-layout>
