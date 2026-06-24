<x-layout>
    <div class="previous-layout-classes">
        <div class="card">
            <div class="text-center py-8">
                <!-- Success icon -->
                <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>

                <!-- Success message -->
                <h1 class="text-2xl font-bold text-slate-800 mb-2">{{ __('federation_request.request_submitted_title') }}</h1>
                <p class="text-slate-600 mb-8 max-w-2xl mx-auto">{{ __('federation_request.request_submitted_message') }}</p>

                <!-- Next steps information box -->
                <div class="information-box text-left max-w-2xl mx-auto mb-8">
                    <h3 class="font-semibold text-slate-800 mb-3">{{ __('federation_request.next_steps_title') }}</h3>
                    <ul class="text-sm text-slate-600 space-y-2">
                        <li class="flex">
                            <span class="text-slate-400 mr-2">•</span>
                            <span>{{ __('federation_request.next_step_review') }}</span>
                        </li>
                        <li class="flex">
                            <span class="text-slate-400 mr-2">•</span>
                            <span>{{ __('federation_request.next_step_notification') }}</span>
                        </li>
                        <li class="flex">
                            <span class="text-slate-400 mr-2">•</span>
                            <span>{{ __('federation_request.next_step_payment') }}</span>
                        </li>
                    </ul>
                </div>

                <!-- Action buttons -->
                <div class="flex flex-wrap justify-center gap-2">
                    <a href="{{ route('individual.dashboard') }}" class="btn btn-secondary">
                        {{ __('common.back') }}
                    </a>
                    <a href="{{ route('individual.federation.index') }}" class="btn btn-primary">
                        {{ __('federation_request.view_federations') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layout>