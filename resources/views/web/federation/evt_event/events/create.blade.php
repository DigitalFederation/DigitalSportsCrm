@section('title', __('events.form.create_event'))
<x-layout>
    <div class="previous-layout-classes">

        <div class="sm:flex sm:justify-between sm:items-center mb-5 mt-5">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('events.form.create_event') }}</h1>
                <p class="text-sm text-slate-500 mt-1">
                    {{ __('events.category.' . $category) }}
                </p>
            </div>
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a href="{{ route('federation.evt-events.events.index') }}" class="btn btn-secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    {{ __('common.back') }}
                </a>
            </div>
        </div>

        <form action="{{ route('federation.evt-events.events.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            @include('web.admin.evt_events.events.partials.form')
        </form>

    </div>
</x-layout>
