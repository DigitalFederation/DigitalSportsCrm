<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 sm:flex sm:justify-between sm:items-center">
            <!-- Title -->
            <h1 class="page-first-title">{{ __('main.insurance_plan_details') }}</h1>
            <!-- Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a class="btn btn-primary" href="{{ route('admin.insurance-plans.edit', $insurance_plan->id) }}">
                    <span>{{ __('main.edit') }}</span>
                </a>
            </div>
        </div>

        <section class="card">
            <!-- Basic Information -->
            <div class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4 my-5">
                <div class="sm:w-1/3">
                    <label class="block text-sm font-medium mb-1 text-gray-500">{{ __('main.name') }}</label>
                    <p class="text-sm font-semibold">{{ $insurance_plan->name ?? '-' }}</p>
                </div>

                <div class="sm:w-1/3">
                    <label class="block text-sm font-medium mb-1 text-gray-500">{{ __('main.target_audience') }}</label>
                    <p class="text-sm font-semibold">
                        @if($insurance_plan->target_audience)
                            {{ \App\Enums\InsurancePlansTargetAudienceEnum::from($insurance_plan->target_audience)->toString() }}
                        @else
                            -
                        @endif
                    </p>
                </div>

                <div class="sm:w-1/3">
                    <label class="block text-sm font-medium mb-1 text-gray-500">{{ __('main.type') }}</label>
                    <p class="text-sm font-semibold">
                        {{ $insurance_plan->type?->toString() ?? '-' }}
                    </p>
                </div>
            </div>

            <!-- Duration -->
            <div class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4 my-5">
                <div class="sm:w-1/4">
                    <label class="block text-sm font-medium mb-1 text-gray-500">{{ __('main.duration_months') }}</label>
                    <p class="text-sm font-semibold">
                        @if($insurance_plan->period)
                            {{ $insurance_plan->period }} {{ __($insurance_plan->period_unit ? ucfirst($insurance_plan->period_unit) . '(s)' : '') }}
                        @else
                            -
                        @endif
                    </p>
                </div>
            </div>

            <!-- Fees -->
            <div class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4 my-5">
                <div class="sm:w-1/5">
                    <label class="block text-sm font-medium mb-1 text-gray-500">{{ __('main.individual_fee') }} </label>
                    <p class="text-sm font-semibold">{{ $insurance_plan->individual_fee ? money($insurance_plan->individual_fee) : '-' }}</p>
                </div>
                <div class="sm:w-1/5">
                    <label class="block text-sm font-medium mb-1 text-gray-500">{{ __('main.entity_fee') }}</label>
                    <p class="text-sm font-semibold">{{ $insurance_plan->entity_fee ? money($insurance_plan->entity_fee) : '-' }}</p>
                </div>
                <div class="sm:w-1/5">
                    <label class="block text-sm font-medium mb-1 text-gray-500">{{ __('main.vat_rate') }}</label>
                    <p class="text-sm font-semibold">
                        @if($insurance_plan->vat_rate !== null)
                            {{ \Domain\Memberships\Enums\VatRate::options()[$insurance_plan->vat_rate] ?? $insurance_plan->vat_rate . '%' }}
                        @else
                            -
                        @endif
                    </p>
                </div>
                <div class="sm:w-1/5">
                    <label class="block text-sm font-medium mb-1 text-gray-500">{{ __('moloni.product_reference') }}</label>
                    <p class="text-sm font-semibold">{{ $insurance_plan->moloni_reference ?? '-' }}</p>
                </div>
            </div>

            <!-- Dates -->
            <div class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4 my-5">
                <div class="sm:w-1/5">
                    <label class="block text-sm font-medium mb-1 text-gray-500">{{ __('main.start_date') }}</label>
                    <p class="text-sm font-semibold">{{ $insurance_plan->start_date ? $insurance_plan->start_date->format('d/m/Y') : '-' }}</p>
                </div>
                <div class="sm:w-1/5">
                    <label class="block text-sm font-medium mb-1 text-gray-500">{{ __('main.end_date') }}</label>
                    <p class="text-sm font-semibold">{{ $insurance_plan->end_date ? $insurance_plan->end_date->format('d/m/Y') : '-' }}</p>
                </div>
            </div>

            <!-- Attachments -->
            @if ($insurance_plan->getMedia('insurance_attachments')->count() > 0)
                <div class="my-5">
                    <label class="block text-sm font-medium mb-2 text-gray-500">{{ __('main.attachments') }}</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($insurance_plan->getMedia('insurance_attachments') as $attachment)
                            <a href="{{ route('admin.insurance-plans.download', ['id' => $insurance_plan->id, 'mediaId' => $attachment->id]) }}"
                                class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100">
                                <i class="fas fa-paperclip mr-2"></i>
                                {{ $attachment->file_name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Description & Insured Activity -->
            <div class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4 my-5">
                <div class="sm:w-1/2">
                    <label class="block text-sm font-medium mb-1 text-gray-500">{{ __('main.description') }}</label>
                    <p class="text-sm">{{ $insurance_plan->description ?? '-' }}</p>
                </div>
                <div class="sm:w-1/2">
                    <label class="block text-sm font-medium mb-1 text-gray-500">{{ __('main.insured_activity') }}</label>
                    <p class="text-sm">{{ $insurance_plan->insured_activity ?? '-' }}</p>
                </div>
            </div>

            <!-- Territorial Scope & international License Code -->
            <div class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4 my-5">
                <div class="sm:w-1/2">
                    <label class="block text-sm font-medium mb-1 text-gray-500">{{ __('main.territorial_scope') }}</label>
                    <p class="text-sm">{{ $insurance_plan->territorial_scope ?? '-' }}</p>
                </div>
                <div class="sm:w-1/2">
                    <label class="block text-sm font-medium mb-1 text-gray-500">{{ __('main.cmas_license_code') }}</label>
                    <p class="text-sm font-semibold">{{ $insurance_plan->cmas_license_code ?? '-' }}</p>
                </div>
            </div>

            <!-- Policy Number -->
            <div class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4 my-5">
                <div class="sm:w-1/2">
                    <label class="block text-sm font-medium mb-1 text-gray-500">{{ __('main.policy_number') }}</label>
                    <p class="text-sm font-semibold">{{ $insurance_plan->policy_number ?? '-' }}</p>
                </div>
            </div>

            <!-- Insurer Contact Information Section -->
            <div class="border-t pt-4 my-6">
                <h3 class="text-sm font-medium text-gray-900 mb-4">{{ __('insurances.insurer_contact_information') }}</h3>

                <div class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4 mb-4">
                    <div class="sm:w-1/2">
                        <label class="block text-sm font-medium mb-1 text-gray-500">{{ __('insurances.insurance_company_name') }}</label>
                        <p class="text-sm font-semibold">{{ $insurance_plan->insurance_company_name ?? '-' }}</p>
                    </div>
                </div>

                <div class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4">
                    <div class="sm:w-1/3">
                        <label class="block text-sm font-medium mb-1 text-gray-500">{{ __('insurances.insurer_email') }}</label>
                        <p class="text-sm font-semibold">{{ $insurance_plan->insurer_email ?? '-' }}</p>
                    </div>

                    <div class="sm:w-1/3">
                        <label class="block text-sm font-medium mb-1 text-gray-500">{{ __('insurances.insurer_phone') }}</label>
                        <p class="text-sm font-semibold">{{ $insurance_plan->insurer_phone ?? '-' }}</p>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium mb-1 text-gray-500">{{ __('insurances.insurer_address') }}</label>
                    <p class="text-sm">{{ $insurance_plan->insurer_address ?? '-' }}</p>
                </div>
            </div>

            <!-- Coverage Details Section -->
            <div class="border-t pt-4 my-6">
                <h3 class="text-sm font-medium text-gray-900 mb-4">{{ __('insurances.coverage_information') }}</h3>

                <div class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4">
                    <div class="sm:w-1/2">
                        <label class="block text-sm font-medium mb-1 text-gray-500">{{ __('insurances.applicable_deductibles') }}</label>
                        <p class="text-sm">{{ $insurance_plan->applicable_deductibles ?? '-' }}</p>
                    </div>

                    <div class="sm:w-1/2">
                        <label class="block text-sm font-medium mb-1 text-gray-500">{{ __('insurances.coverage_details') }}</label>
                        <p class="text-sm">{{ $insurance_plan->coverage_details ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Sequential Policy Number Settings -->
            <div class="border-t pt-4 my-6">
                <h3 class="text-sm font-medium text-gray-900 mb-4">{{ __('main.sequential_policy_number_settings') }}</h3>

                <div class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4">
                    <div class="sm:w-1/3">
                        <label class="block text-sm font-medium mb-1 text-gray-500">{{ __('main.policy_number_prefix') }}</label>
                        <p class="text-sm font-semibold">{{ $insurance_plan->policy_number_prefix ?? '-' }}</p>
                    </div>

                    <div class="sm:w-1/3">
                        <label class="block text-sm font-medium mb-1 text-gray-500">{{ __('main.policy_number_format') }}</label>
                        <p class="text-sm font-semibold">{{ $insurance_plan->policy_number_format ?? '-' }}</p>
                    </div>

                    <div class="sm:w-1/3">
                        <label class="block text-sm font-medium mb-1 text-gray-500">{{ __('main.current_sequence_number') }}</label>
                        <p class="text-sm font-semibold">{{ $insurance_plan->policy_number_sequence ?? '0' }}</p>
                    </div>
                </div>
            </div>

            <!-- Official Document Requirements Section -->
            <div class="border-t pt-4 my-6">
                <h3 class="text-sm font-medium text-gray-900 mb-4">{{ __('main.official_document_requirements') }}</h3>

                <div class="sm:flex sm:items-top space-y-4 sm:space-y-0 sm:space-x-4">
                    <div class="sm:w-1/2">
                        <label class="block text-sm font-medium mb-1 text-gray-500">{{ __('main.requires_official_document') }}</label>
                        <p class="text-sm font-semibold">
                            @if($insurance_plan->requires_official_document)
                                <span class="text-green-600">{{ __('main.yes') }}</span>
                            @else
                                <span class="text-gray-500">{{ __('main.no') }}</span>
                            @endif
                        </p>
                    </div>

                    <div class="sm:w-1/2">
                        <label class="block text-sm font-medium mb-1 text-gray-500">{{ __('main.required_document_type') }}</label>
                        <p class="text-sm font-semibold">
                            @if($insurance_plan->required_document_type)
                                {{ \App\Enums\OfficialDocumentTypeEnum::toString(\App\Enums\OfficialDocumentTypeEnum::from($insurance_plan->required_document_type)) }}
                            @else
                                -
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Affiliation Requirements Section -->
            <div class="border-t pt-4 my-6">
                <h3 class="text-sm font-medium text-gray-900 mb-4">{{ __('main.affiliation_requirements') }}</h3>

                <div class="sm:w-full">
                    <label class="block text-sm font-medium mb-1 text-gray-500">{{ __('main.requires_active_affiliation') }}</label>
                    <p class="text-sm font-semibold">
                        @if($insurance_plan->requires_active_affiliation)
                            <span class="text-green-600">{{ __('main.yes') }}</span>
                        @else
                            <span class="text-gray-500">{{ __('main.no') }}</span>
                        @endif
                    </p>
                </div>
            </div>

            <!-- Back Button -->
            <div class="flex justify-end mt-6">
                <a href="{{ route('admin.insurance-plans.index') }}" class="btn btn-secondary">
                    {{ __('main.back') }}
                </a>
            </div>
        </section>
    </div>
</x-layout>
