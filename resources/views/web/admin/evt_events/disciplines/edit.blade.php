<x-layout>
    <div class="previous-layout-classes">

        <div class="mb-8">
            <h1 class="page-first-title">
                {{ __('Edit Discipline') }}
            </h1>
            <p class="font-normal">{{ $competition->full_name }}</p>
        </div>

        <form
            action="{{ route('admin.evt-events.disciplines.update', ['competition' => $competition->id, 'discipline' => $discipline->id]) }}"
            method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" value="{{ $competition->id }}" name="competition_id" id="competition_id">
            <x-information-box
                :title="__('Team and Relay Enrollment Rules')"
                :body="__('events.team_relay_rules_body')" />

            <div class="card w-full flex flex-col md:flex-row md:-mr-px">
                @include('web.admin.evt_events.disciplines.partials.form')
            </div>
        </form>
    </div>
</x-layout>
