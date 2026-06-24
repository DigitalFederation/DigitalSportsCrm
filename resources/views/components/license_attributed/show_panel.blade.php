<div class="w-full">

    <!-- Status ribbon -->
    <div class="absolute right-0 top-0 h-16 w-16">
        <div
            class="text-sm absolute transform rotate-45 bg-{{ $license->stateColor() }} text-center text-white font-semibold py-1 right-[-35px] top-[35px] w-[170px]">
            {{ ucfirst($license->stateName()) }}
        </div>
    </div>

    @php
        $userGroup = auth()->user()->group()->first()->code;
        $licenseState = $license->stateName();
        $isPending = $license->status_class == \Domain\Licenses\States\PendingLicenseAttributedState::class;
        $isSuspended = $license->status_class == \Domain\Licenses\States\SuspendedLicenseAttributedState::class;
        $isActive = $license->status_class == \Domain\Licenses\States\ActiveLicenseAttributedState::class;
        $isWaitingApproval = $license->status_class == \Domain\Licenses\States\WaitingApprovalLicenseAttributedState::class;
        $isProvisional = $license->status_class == \Domain\Licenses\States\ProvisionalLicenseAttributedState::class;
        $isPendingValidation = $license->status_class == \Domain\Licenses\States\PendingValidationLicenseAttributedState::class;
    @endphp

    <!-- Status alert (if applicable) -->
    @if($isPending || $isWaitingApproval || $isProvisional || $isPendingValidation)
        <div class="mb-6">
            @if($isPending)
                <div class="text-sm text-amber-600 bg-amber-50 border border-amber-200 p-4 rounded-lg">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ __('licenses.pending_payment_message') }}</span>
                    </div>
                </div>
            @elseif($isWaitingApproval)
                <div class="text-sm text-blue-600 bg-blue-50 border border-blue-200 p-4 rounded-lg">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ __('licenses.waiting_approval_message') }}</span>
                    </div>
                </div>
            @elseif($isProvisional)
                <div class="text-sm text-yellow-600 bg-yellow-50 border border-yellow-200 p-4 rounded-lg">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ __('licenses.provisional_message') }}</span>
                    </div>
                </div>
            @elseif($isPendingValidation)
                <div class="text-sm text-indigo-600 bg-indigo-50 border border-indigo-200 p-4 rounded-lg">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ __('licenses.license_pending_validation_requires_approval') }}</span>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- License details -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- License Information -->
        <div class="space-y-4">
            <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider border-b pb-2">
                {{ __('licenses.License Information') }}
            </h3>
            <div class="space-y-3">
                <div>
                    <div class="text-secondary font-semibold">{{ __('License Name')}}</div>
                    <p class="text-slate-500">{{ $license->license->name }}</p>
                </div>
                <div>
                    <div class="text-secondary font-semibold">{{ __('licenses.license_number') }}</div>
                    <p class="text-slate-500">
                        {{ !empty($license->license_number) ? $license->license_number : __('Not defined') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Validity Period -->
        <div class="space-y-4">
            <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider border-b pb-2">
                {{ __('licenses.validity') }}
            </h3>
            <div class="space-y-3">
                <div>
                    <div class="text-secondary font-semibold">{{ __('licenses.start_date') }}</div>
                    @if($license->current_term_starts_at)
                        <p class="text-slate-500">{{ $license->current_term_starts_at->format('d/m/Y') }}</p>
                    @else
                        <p class="text-slate-500">{{ __('Not defined') }}</p>
                    @endif
                </div>
                <div>
                    <div class="text-secondary font-semibold">{{ __('licenses.expiry_date') }}</div>
                    @if($license->current_term_ends_at)
                        <p class="text-slate-500">{{ $license->current_term_ends_at->format('d/m/Y') }}</p>
                    @else
                        <p class="text-slate-500">{{ __('No expiration date') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Holder Information -->
        @if($license->manualOwner instanceof \Domain\Individuals\Models\Individual)
            <div class="space-y-4 md:col-span-2">
                <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider border-b pb-2">
                    {{ __('licenses.License Holder') }}
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <div class="text-secondary font-semibold">{{ __('Individual Name')}}</div>
                        <a href="{{ route(Request::segment(1).'.individual.show', $license->manualOwner->id)}}"
                           target="_blank" class="text-slate-500 hover:underline hover:text-primary block">
                            {{ $license->manualOwner->full_name }}
                        </a>
                    </div>
                    <div>
                        <div class="text-secondary font-semibold">{{ __('Individual Birthdate')}}</div>
                        <p class="text-slate-500">{{ \Carbon\Carbon::parse($license->manualOwner->birthdate)->format('d/m/Y') }}</p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Actions -->
    <div class="mt-8 pt-6 border-t border-slate-200">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <!-- Status-specific actions -->
            <div class="flex flex-wrap gap-2">
                @if(in_array($userGroup, ['ADMIN', 'FEDERATION']))
                    @if($isActive)
                        <form id="suspendLicenseForm" action="{{ route(Request::segment(1).'.license-suspend.store') }}"
                              method="post"
                              onsubmit="return confirm('{{ __('licenses.confirm_suspend') }}')">
                            @csrf
                            <input type="hidden" name="license_id" id="license_id" value="{{ $license->id }}">
                            <button type="submit" class="btn btn-danger">{{ __('licenses.suspend_license') }}</button>
                        </form>
                    @endif

                    @if($isSuspended)
                        <form action="{{ route(Request::segment(1).'.license-attributed.activate', $license->id) }}"
                              method="POST"
                              onsubmit="return confirm('{{ __('licenses.confirm_reactivate') }}')">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-success">{{ __('licenses.reactivate_license') }}</button>
                        </form>
                    @endif

                    @if($isPending)
                        <form action="{{ route(Request::segment(1).'.license-attributed.activate', $license->id) }}"
                              method="POST"
                              onsubmit="return confirm('{{ __('licenses.confirm_manual_activate') }}')">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-success">{{ __('licenses.manually_activate') }}</button>
                        </form>
                        <form action="{{ route(Request::segment(1).'.license-attributed.cancel', $license->id) }}"
                              method="POST"
                              onsubmit="return confirm('{{ __('licenses.confirm_cancel') }}')">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-danger">{{ __('licenses.cancel_license') }}</button>
                        </form>
                    @endif

                    @if($isWaitingApproval)
                        <form action="{{ route(Request::segment(1).'.license-attributed.approve', $license->id) }}"
                              method="POST"
                              onsubmit="return confirm('{{ __('licenses.confirm_approve') }}')">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-primary">{{ __('licenses.approve_license') }}</button>
                        </form>
                        <form action="{{ route(Request::segment(1).'.license-attributed.cancel', $license->id) }}"
                              method="POST"
                              onsubmit="return confirm('{{ __('licenses.confirm_reject') }}')">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-danger">{{ __('licenses.reject_license') }}</button>
                        </form>
                    @endif

                    @if($isProvisional)
                        <form action="{{ route(Request::segment(1).'.license-attributed.activate', $license->id) }}"
                              method="POST"
                              onsubmit="return confirm('{{ __('licenses.confirm_activate_provisional') }}')">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-success">{{ __('licenses.activate_provisional') }}</button>
                        </form>
                    @endif

                    @if($isPendingValidation)
                        <form action="{{ route(Request::segment(1).'.license-attributed.approve', $license->id) }}"
                              method="POST"
                              onsubmit="return confirm('{{ __('licenses.confirm_validate_approve') }}')">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-primary">{{ __('licenses.validate_and_approve') }}</button>
                        </form>
                        <form action="{{ route(Request::segment(1).'.license-attributed.cancel', $license->id) }}"
                              method="POST"
                              onsubmit="return confirm('{{ __('licenses.confirm_reject_validation') }}')">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-danger">{{ __('licenses.reject_validation') }}</button>
                        </form>
                    @endif
                @endif
            </div>

            <!-- Back button -->
            <a href="{{ URL::previous() }}" class="btn btn-info">{{ __('Back') }}</a>
        </div>
    </div>

</div>
