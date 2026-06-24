<x-layout>
    <!-- Page header -->
    <div class="mb-8 flex justify-between">
        <!-- Title -->
        <h1 class="page-first-title">{{ __('certifications.edit.title') }}</h1>
    </div>


    <div class="card w-full overflow-hidden">
        <div class="card-body">
            <form
                action="{{ route('admin.certification-attributed.update', $certificationAttributed->id) }}"
                method="POST">
                @csrf
                @method('PUT')

                <div class="flex gap-x-4 mb-6 border-b border-slate-400">
                    <div class="text-lg font-bold">
                        {{ $certificationAttributed->certification->name }}
                    </div>
                    <div class="text-lg">
                        {{ $certificationAttributed->holder_name }}
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- National Certification Number -->
                    <div>
                        <label for="national_code" class="block text-sm font-medium text-gray-700">{{ __('certifications.edit.national_certification_number') }}</label>
                        <input type="text" name="national_code" id="national_code"
                               class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                               value="{{ $certificationAttributed->national_code }}">
                    </div>

                    <!-- Issue Date -->
                    <div>
                        <label for="current_term_starts_at" class="block text-sm font-medium text-gray-700">{{ __('certifications.edit.issue_date') }}</label>
                        <input type="date" name="current_term_starts_at" id="current_term_starts_at"
                               class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                               value="{{ $certificationAttributed->current_term_starts_at ? $certificationAttributed->current_term_starts_at->format('Y-m-d') : '' }}">
                    </div>

                    <!-- Expire Date -->
                    <div>
                        <label for="current_term_ends_at" class="block text-sm font-medium text-gray-700">{{ __('certifications.edit.expire_date') }}</label>
                        <input type="date" name="current_term_ends_at" id="current_term_ends_at"
                               class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                               value="{{ $certificationAttributed->current_term_ends_at ? $certificationAttributed->current_term_ends_at->format('Y-m-d') : '' }}">
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status_class" class="block text-sm font-medium text-gray-700">{{ __('certifications.edit.status') }}</label>
                        <select name="status_class" id="status_class"
                                class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            @php
                                $states = [
                                    \Domain\Certifications\States\ActiveCertificationAttributedState::class => __('certifications.details.states.active'),
                                    \Domain\Certifications\States\PendingCertificationAttributedState::class => __('certifications.details.states.pending'),
                                    \Domain\Certifications\States\ProvisionalCertificationAttributedState::class => __('certifications.details.states.provisional'),
                                    \Domain\Certifications\States\CanceledCertificationAttributedState::class => __('certifications.details.states.canceled'),
                                    \Domain\Certifications\States\ExpiredCertificationAttributedState::class => __('certifications.details.states.expired'),
                                    \Domain\Certifications\States\SuspendedCertificationAttributedState::class => __('certifications.details.states.suspended'),
                                    \Domain\Certifications\States\RejectedCertificationAttributedState::class => __('certifications.details.states.rejected'),
                                    \Domain\Certifications\States\DirectorApprovalCertificationAttributedState::class => __('certifications.details.states.director_approval'),
                                    \Domain\Certifications\States\DirectorApprovedCertificationAttributedState::class => __('certifications.details.states.director_approved'),
                                ];
                            @endphp
                            @foreach ($states as $stateClass => $label)
                                <option value="{{ $stateClass }}" @selected($certificationAttributed->status_class === $stateClass)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Notes -->
                    <div class="md:col-span-2">
                        <label for="notes" class="block text-sm font-medium text-gray-700">{{ __('certifications.edit.notes') }}</label>
                        <textarea
                            name="notes"
                            id="notes"
                            rows="3"
                            class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 mt-1 block w-full sm:text-sm border-gray-300 rounded-md">{{ $certificationAttributed->notes }}</textarea>
                    </div>
                </div>

                <div class="mt-4 flex justify-start">
                    <x-forms.card-form-submit backRoute="federation.certification-attributed.index"
                                              :buttonText="__('certifications.edit.save_changes')"></x-forms.card-form-submit>
                </div>
            </form>
        </div>
    </div>
</x-layout>
