@props(['event'])

@php
    $technicalDelegate = $event->technicalDelegate ?? null;
    $chiefJudge = $event->chiefJudge ?? null;
    $competitionDirector = $event->competitionDirector ?? null;
@endphp

<div class="card" x-data="{
    technicalDelegate: '{{ old('technical_delegate_id', $technicalDelegate?->individual_id) }}',
    chiefJudge: '{{ old('chief_judge_id', $chiefJudge?->individual_id) }}',
    competitionDirector: '{{ old('competition_director_id', $competitionDirector?->individual_id) }}',
    technicalDelegateName: '{{ old('technical_delegate_name', $technicalDelegate?->individual?->full_name) }}',
    chiefJudgeName: '{{ old('chief_judge_name', $chiefJudge?->individual?->full_name) }}',
    competitionDirectorName: '{{ old('competition_director_name', $competitionDirector?->individual?->full_name) }}',
    checkDuplicates() {
        const values = [this.technicalDelegate, this.chiefJudge, this.competitionDirector].filter(v => v);
        const unique = [...new Set(values)];
        if (values.length !== unique.length) {
            alert('{{ __('events.same_person_multiple_roles_error') }}');
            return false;
        }
        return true;
    }
}"
x-init="
    $watch('technicalDelegate', () => checkDuplicates());
    $watch('chiefJudge', () => checkDuplicates());
    $watch('competitionDirector', () => checkDuplicates());
"
x-on:individual-selected.window="
    if ($event.detail.inputId === 'technical_delegate_selector') {
        technicalDelegate = $event.detail.id;
        technicalDelegateName = $event.detail.name;
    } else if ($event.detail.inputId === 'chief_judge_selector') {
        chiefJudge = $event.detail.id;
        chiefJudgeName = $event.detail.name;
    } else if ($event.detail.inputId === 'competition_director_selector') {
        competitionDirector = $event.detail.id;
        competitionDirectorName = $event.detail.name;
    }
">
    <div class="flex gap-x-2 items-center border-b border-gray-300 pb-2 mb-4">
        <x-svg.info class="w-6 h-6 text-slate-600" />
        <span class="font-bold">{{ __('events.event_management_roles') }}</span>
    </div>

    <div class="mb-4">
        <label for="technical_delegate_id" class="block text-sm font-medium mb-1">
            {{ __('events.technical_delegate') }}
        </label>
        <div class="flex gap-2">
            <input type="hidden" name="technical_delegate_id" :value="technicalDelegate">
            <div class="flex-1 p-3 border border-gray-300 rounded-md bg-gray-50 text-sm"
                 x-text="technicalDelegateName || '{{ __('events.no_individual_selected') }}'"></div>
            <x-event-individual-selector input-id="technical_delegate_selector" />
            <button type="button" 
                    x-show="technicalDelegate"
                    @click="technicalDelegate = ''; technicalDelegateName = ''"
                    class="btn btn-danger h-[38px]">
                {{ __('common.clear') }}
            </button>
        </div>
        @error('technical_delegate_id')
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
        <p class="text-xs text-gray-600 mt-1">
            {{ __('events.has_view_only_access') }}
        </p>
    </div>

    <div class="mb-4">
        <label for="chief_judge_id" class="block text-sm font-medium mb-1">
            {{ __('events.chief_judge') }}
        </label>
        <div class="flex gap-2">
            <input type="hidden" name="chief_judge_id" :value="chiefJudge">
            <div class="flex-1 p-3 border border-gray-300 rounded-md bg-gray-50 text-sm"
                 x-text="chiefJudgeName || '{{ __('events.no_individual_selected') }}'"></div>
            <x-event-individual-selector input-id="chief_judge_selector" />
            <button type="button" 
                    x-show="chiefJudge"
                    @click="chiefJudge = ''; chiefJudgeName = ''"
                    class="btn btn-danger h-[38px]">
                {{ __('common.clear') }}
            </button>
        </div>
        @error('chief_judge_id')
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
        <p class="text-xs text-gray-600 mt-1">
            {{ __('events.manages_post_event_functions') }}
        </p>
    </div>

    <div class="mb-4">
        <label for="competition_director_id" class="block text-sm font-medium mb-1">
            {{ __('events.competition_director') }}
        </label>
        <div class="flex gap-2">
            <input type="hidden" name="competition_director_id" :value="competitionDirector">
            <div class="flex-1 p-3 border border-gray-300 rounded-md bg-gray-50 text-sm"
                 x-text="competitionDirectorName || '{{ __('events.no_individual_selected') }}'"></div>
            <x-event-individual-selector input-id="competition_director_selector" />
            <button type="button" 
                    x-show="competitionDirector"
                    @click="competitionDirector = ''; competitionDirectorName = ''"
                    class="btn btn-danger h-[38px]">
                {{ __('common.clear') }}
            </button>
        </div>
        @error('competition_director_id')
            <span class="text-red-500 text-sm">{{ $message }}</span>
        @enderror
        <p class="text-xs text-gray-600 mt-1">
            {{ __('events.public_facing_role') }}
        </p>
    </div>

</div>