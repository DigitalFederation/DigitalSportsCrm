@props(['evaluation' => null, 'evaluationNotes' => null])

<div class="space-y-4">
    @if($evaluation)
        <div>
            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('events.evaluation') }}</h4>
            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ $evaluation }} - {{ \App\Livewire\EvtEvents\JudgeEnrollments::getEvaluationLabel((int) $evaluation) }}
            </p>
        </div>
    @endif

    @if($evaluationNotes)
        <div>
            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('events.evaluation_notes') }}</h4>
            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ $evaluationNotes }}</p>
        </div>
    @endif
</div>
