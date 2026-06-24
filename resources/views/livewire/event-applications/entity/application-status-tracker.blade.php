<div class="card">
    @php
        $currentState = $application->state->name();
        $isSpecialState = in_array($currentState, ['returned_for_correction', 'rejected']);
    @endphp

    @if($isSpecialState)
        <!-- Special State Display -->
        <div class="text-center py-6">
            @if($currentState === 'returned_for_correction')
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-orange-100 mb-4">
                    <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-800 mb-2">
                    {{ __('event_applications.states.returned_for_correction') }}
                </h3>
                <p class="text-sm text-slate-600">
                    {{ __('event_applications.state_descriptions.returned_for_correction') }}
                </p>
            @elseif($currentState === 'rejected')
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-100 mb-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-800 mb-2">
                    {{ __('event_applications.states.rejected') }}
                </h3>
                <p class="text-sm text-slate-600">
                    {{ __('event_applications.state_descriptions.rejected') }}
                </p>
            @endif
        </div>
    @else
        <!-- Linear Progress Tracker -->
        <div class="relative">
            <!-- Progress Bar -->
            <div class="absolute left-0 top-8 h-0.5 bg-slate-200 w-full"></div>
            <div class="absolute left-0 top-8 h-0.5 bg-blue-500 transition-all duration-500"
                 style="width: {{ $currentStateIndex > 0 ? ($currentStateIndex / (count($states) - 1)) * 100 : 0 }}%"></div>

            <!-- Steps -->
            <div class="relative flex justify-between">
                @foreach($states as $index => $state)
                    @php
                        $isCompleted = $index < $currentStateIndex;
                        $isCurrent = $index === $currentStateIndex;
                        $isPending = $index > $currentStateIndex;

                        $iconColor = $isCompleted || $isCurrent ? 'text-white' : 'text-slate-400';
                        $bgColor = $isCompleted ? 'bg-blue-500' : ($isCurrent ? 'bg-blue-600' : 'bg-slate-200');
                        $textColor = $isCurrent ? 'text-slate-800 font-semibold' : 'text-slate-600';
                    @endphp

                    <div class="flex flex-col items-center" style="width: {{ 100 / count($states) }}%">
                        <!-- Icon Circle -->
                        <div class="flex items-center justify-center w-16 h-16 rounded-full {{ $bgColor }} mb-2 shadow-lg z-10">
                            @if($isCompleted)
                                <svg class="w-6 h-6 {{ $iconColor }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            @else
                                @if($state['key'] === 'draft')
                                    <svg class="w-6 h-6 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                @elseif($state['key'] === 'submitted')
                                    <svg class="w-6 h-6 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                    </svg>
                                @elseif($state['key'] === 'approved')
                                    <svg class="w-6 h-6 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                @endif
                            @endif
                        </div>

                        <!-- Label -->
                        <p class="text-xs text-center {{ $textColor }} max-w-24">
                            {{ $state['label'] }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Current State Description -->
        <div class="mt-8 pt-6 border-t border-slate-200 text-center">
            <p class="text-sm text-slate-600">
                {{ __('event_applications.state_descriptions.' . $currentState) }}
            </p>
        </div>
    @endif
</div>
