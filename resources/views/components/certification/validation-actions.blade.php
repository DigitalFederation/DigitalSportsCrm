@props(['certification'])

@php
    use Domain\Certifications\States\PendingCertificationAttributedState;
    use Domain\Certifications\States\DirectorApprovalCertificationAttributedState;
    use Domain\Certifications\States\DirectorApprovedCertificationAttributedState;
    use Domain\Certifications\States\ActiveCertificationAttributedState;
    use Domain\Certifications\States\RejectedCertificationAttributedState;

    $authenticatedIndividual = auth()->user()->individuals()->first();
    $mainInstructor = $certification->mainInstructor->first();

    $canValidate = $authenticatedIndividual && $mainInstructor && $authenticatedIndividual->id === $mainInstructor->id;
    $currentState = $certification->status_class;

    // States where instructor can take action
    $canTakeAction = in_array($currentState, [
        PendingCertificationAttributedState::class,
        DirectorApprovalCertificationAttributedState::class
    ]);
@endphp

@if($canValidate && $canTakeAction)
    <div class="card mt-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-slate-800">{{ __('certifications.validation.instructor_actions_title') }}</h2>
        </div>

        <div class="p-4 bg-blue-50 border-l-4 border-blue-400 text-sm text-blue-700 mb-4">
            <p class="font-medium mb-1">{{ __('certifications.validation.as_instructor_can') }}</p>
            <ul class="list-disc list-inside ml-2">
                <li>{{ __('certifications.validation.approve_description') }}</li>
                <li>{{ __('certifications.validation.reject_description') }}</li>
            </ul>
            <p class="mt-2 text-xs">{{ __('certifications.validation.actions_warning') }}</p>
        </div>

        <div class="mt-4 flex space-x-4">
            @if($currentState !== DirectorApprovedCertificationAttributedState::class &&
                $currentState !== ActiveCertificationAttributedState::class &&
                $currentState !== RejectedCertificationAttributedState::class)
                <form action="{{ route('individual.certification-validate.activate', $certification) }}" method="POST"
                      class="inline">
                    @csrf
                    <button class="btn btn-primary" type="submit">
                        {{ __('certifications.validation.approve_button') }}
                    </button>
                </form>

                <form action="{{ route('individual.certification-validate.reject', $certification) }}" method="POST"
                      class="inline">
                    @csrf
                    <button class="btn btn-danger" type="submit">
                        {{ __('certifications.validation.reject_button') }}
                    </button>
                </form>
            @else
                <div class="p-4 bg-gray-50 text-sm text-gray-600">
                    {{ __('certifications.validation.no_actions_available', ['state' => $certification->stateName()]) }}
                </div>
            @endif
        </div>
    </div>
@endif
