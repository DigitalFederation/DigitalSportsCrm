<div>
    <h2 class="text-xl font-bold">{{ ucfirst($enrollmentType) }} {{ __('events.enrollment') }}</h2>

    @if($enrollmentType === 'Athlete')
        <!-- Select Discipline -->
        <div>
            <label for="discipline">{{ __('events.select_discipline') }}</label>
            <select wire:model="selectedDiscipline">
                <option value="">-- {{ __('events.select_discipline') }} --</option>
                @foreach($disciplines as $discipline)
                    <option value="{{ $discipline->id }}">{{ $discipline->name }}</option>
                @endforeach
            </select>
        </div>
    @endif

    <div class="mt-4">
        <input type="text" wire:model="search" placeholder="{{ __('events.search_individuals_placeholder') }}">
    </div>

    <table class="mt-4">
        <thead>
        <tr>
            <th>{{ __('events.select') }}</th>
            <th>{{ __('events.name') }}</th>
            <th>{{ __('certifications.member_code') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($individuals as $individual)
            <tr>
                <td>
                    <input type="checkbox" wire:click="toggleIndividualSelection({{ $individual->id }})"
                           @if(in_array($individual->id, $selectedIndividuals)) checked @endif>
                </td>
                <td>{{ $individual->full_name }}</td>
                <td>{{ $individual->member_code }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    @if(!empty($selectedIndividuals))
        <div class="mt-4">
            <h3>{{ __('events.selected_participants', ['type' => ucfirst($enrollmentType) . 's']) }}</h3>
            <ul>
                @foreach($selectedIndividuals as $individualId)
                    <li>{{ $individuals->find($individualId)->full_name }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mt-4">
        <button wire:click="doShowConfirmation" class="btn btn-primary"
                @if(empty($selectedIndividuals)) disabled @endif>
            {{ __('events.submit_enrollment') }}
        </button>
    </div>

    @if($showConfirmation)
        <div class="mt-4">
            <p>{{ __('events.enrollment_confirmation', ['count' => count($selectedIndividuals), 'type' => ucfirst($enrollmentType) . 's', 'total' => $totalCost]) }}</p>
            <button wire:click="submitEnrollment" class="btn btn-success">{{ __('events.confirm') }}</button>
            <button wire:click="$set('showConfirmation', false)" class="btn btn-secondary">{{ __('events.cancel') }}</button>
        </div>
    @endif
</div>
