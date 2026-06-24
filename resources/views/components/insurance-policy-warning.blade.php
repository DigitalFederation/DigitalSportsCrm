@php
    // Logic to determine if warning should be shown
    $shouldShowWarning = !isset($insurance->policy_number) || empty($insurance->policy_number);
@endphp

@if($shouldShowWarning)
    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4" role="alert">
        <p class="font-bold">{{ __('main.warning') }}</p>
        <p>{{ __('main.insurance_policy_number_warning') }}</p>
    </div>
@endif