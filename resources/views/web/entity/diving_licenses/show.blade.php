@section('title', __('diving.diving_license_details'))
<x-layout>
    <div class="previous-layout-classes">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5 mt-5">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('diving.diving_license_details') }}</h1>
                <nav class="mt-2" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2">
                        <li>
                            <a href="{{ route('entity.diving_licenses.index') }}" class="text-gray-500 hover:text-gray-700 text-sm">
                                {{ __('diving.diving_licenses') }}
                            </a>
                        </li>
                        <li class="text-gray-500">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </li>
                        <li class="text-sm text-gray-700">
                            {{ $licenseAttributed->license->name }}
                        </li>
                    </ol>
                </nav>
            </div>
            
            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a href="{{ route('entity.diving_licenses.index') }}"
                   class="inline-flex items-center btn btn-secondary">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    {{ __('diving.back_to_list') }}
                </a>
            </div>
        </div>

        @php
            $activeDirectors = $licenseAttributed->divingTechnicalDirectors()
                ->where('status_class', \Domain\Diving\States\AssignedDivingTechnicalDirectorState::class)
                ->with('individual')
                ->get();
            
            // Collect all unique certification systems
            $allSystems = [];
            foreach ($activeDirectors as $director) {
                if (is_array($director->certification_systems)) {
                    $allSystems = array_merge($allSystems, $director->certification_systems);
                }
            }
            $allSystems = array_unique($allSystems);
        @endphp

        <!-- Validation Status Alert -->
        @if($licenseAttributed->status_class === 'Domain\\Licenses\\States\\PendingValidationLicenseAttributedState')
            <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-amber-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-amber-700">
                            {{ __('diving.license_pending_admin_validation') }}
                        </p>
                    </div>
                </div>
            </div>
        @elseif($licenseAttributed->status_class === 'Domain\\Licenses\\States\\CanceledLicenseAttributedState' && $licenseAttributed->validation_notes)
            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">
                            {{ __('diving.license_validation_rejected') }}
                        </h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p>{{ __('diving.rejection_reason') }}: {{ $licenseAttributed->validation_notes }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @elseif($licenseAttributed->status_class === 'Domain\\Licenses\\States\\PendingLicenseAttributedState')
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            {{ __('diving.license_approved_pending_payment') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Block 1: License Information -->
        <div class="card mt-6">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    {{ __('diving.license_information') }}
                </h3>
            </div>
            <div class="border-t border-gray-200">
                <dl>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            {{ __('diving.license_type') }}
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $licenseAttributed->license->name }}
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            {{ __('diving.license_number') }}
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $licenseAttributed->national_license_code ?? $licenseAttributed->id }}
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            {{ __('diving.approval_date') }}
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            @if($licenseAttributed->validated_at)
                                {{ $licenseAttributed->validated_at->format('d/m/Y') }}
                            @elseif($licenseAttributed->activated_at)
                                {{ $licenseAttributed->activated_at->format('d/m/Y') }}
                            @else
                                {{ __('common.not_available') }}
                            @endif
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            {{ __('diving.expiration_date') }}
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            @if($licenseAttributed->current_term_ends_at)
                                {{ $licenseAttributed->current_term_ends_at->format('d/m/Y') }}
                                @if($licenseAttributed->current_term_ends_at->isPast())
                                    <span class="ml-2 text-red-600 text-xs">{{ __('diving.status_expired') }}</span>
                                @elseif($licenseAttributed->current_term_ends_at->diffInDays(now()) <= 30)
                                    <span class="ml-2 text-yellow-600 text-xs">{{ __('diving.status_expiring_soon') }}</span>
                                @endif
                            @else
                                {{ __('common.not_available') }}
                            @endif
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            {{ __('diving.status') }}
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            @php
                                $statusConfig = [
                                    'Domain\\Licenses\\States\\ActiveLicenseAttributedState' => ['label' => __('diving.status_active'), 'bg' => 'bg-green-100', 'text' => 'text-green-800'],
                                    'Domain\\Licenses\\States\\PendingLicenseAttributedState' => ['label' => __('diving.status_pending'), 'bg' => 'bg-yellow-100', 'text' => 'text-yellow-800'],
                                    'Domain\\Licenses\\States\\CanceledLicenseAttributedState' => ['label' => __('diving.status_canceled'), 'bg' => 'bg-gray-100', 'text' => 'text-gray-800'],
                                    'Domain\\Licenses\\States\\PendingValidationLicenseAttributedState' => ['label' => __('diving.status_pending_validation'), 'bg' => 'bg-amber-100', 'text' => 'text-amber-800'],
                                    'Domain\\Licenses\\States\\PendingTechnicalDirectorApprovalLicenseAttributedState' => ['label' => __('diving.status_waiting_dt_approval'), 'bg' => 'bg-orange-100', 'text' => 'text-orange-800'],
                                    'Domain\\Licenses\\States\\ProvisionalLicenseAttributedState' => ['label' => __('diving.status_provisional'), 'bg' => 'bg-blue-100', 'text' => 'text-blue-800'],
                                    'Domain\\Licenses\\States\\WaitingApprovalLicenseAttributedState' => ['label' => __('diving.status_waiting_approval'), 'bg' => 'bg-indigo-100', 'text' => 'text-indigo-800'],
                                    'Domain\\Licenses\\States\\ExpiredLicenseAttributedState' => ['label' => __('diving.status_expired'), 'bg' => 'bg-red-100', 'text' => 'text-red-800'],
                                    'Domain\\Licenses\\States\\SuspendedLicenseAttributedState' => ['label' => __('diving.status_suspended'), 'bg' => 'bg-gray-100', 'text' => 'text-gray-800'],
                                ];
                                $status = $statusConfig[$licenseAttributed->status_class] ?? ['label' => __('diving.status_unknown'), 'bg' => 'bg-gray-100', 'text' => 'text-gray-800'];
                            @endphp
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $status['bg'] }} {{ $status['text'] }}">
                                {{ $status['label'] }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Block 2: Training Systems -->
        <div class="card mt-6">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    {{ __('diving.training_systems') }}
                </h3>
            </div>
            <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                @if(count($allSystems) > 0)
                    <div class="flex flex-wrap gap-2">
                        @foreach($allSystems as $system)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                {{ $system }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500">{{ __('diving.no_systems_assigned') }}</p>
                @endif
            </div>
        </div>

        <!-- Block 3: Technical Directors -->
        <div class="card mt-6">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    {{ __('diving.technical_directors') }}
                </h3>
            </div>
            <div class="border-t border-gray-200">
                @if($activeDirectors->count() > 0)
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('diving.name') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('diving.member_number') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('diving.id_number') }}
                                </th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">{{ __('diving.actions') }}</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($activeDirectors as $director)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $director->individual->full_name ?? $director->individual->name }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $director->individual->member_number ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $director->individual->doc_ref ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('federation.individual.show', $director->individual) }}"
                                           class="text-blue-600 hover:text-blue-800">
                                            {{ __('diving.view_profile') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="px-4 py-5 sm:px-6">
                        <p class="text-sm text-gray-500">{{ __('diving.no_directors_assigned') }}</p>
                    </div>
                @endif
            </div>
        </div>

    </div>
</x-layout>