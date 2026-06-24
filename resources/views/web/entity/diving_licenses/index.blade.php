@section('title', __('Diving Licenses Management'))
<x-layout>
    <!-- Page header -->
    <div class="sm:flex sm:justify-between sm:items-center">
        <!-- Left: Title -->
        <div class="mb-4 sm:mb-0">
            <h1 class="page-first-title">{{ __('diving.diving_licenses_management') }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ __('diving.manage_diving_licenses') }}</p>
        </div>
        
        <!-- Right: Actions -->
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <a href="{{ route('entity.diving_licenses.request') }}"
               class="inline-flex items-center btn btn-primary">
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                {{ __('diving.request_new_license') }}
            </a>
        </div>
    </div>

    <!-- Information Box -->
    <div class="mt-6">
        <x-information-box 
            title="{{ __('diving.important') }}" 
            body="{!! __('diving.license_director_requirement') !!}">
        </x-information-box>
    </div>

    <div class="mt-6">
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    {{ __('diving.all_diving_licenses') }}
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                    {{ __('diving.entity_diving_licenses_description') }}
                </p>
            </div>
            <div class="border-t border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('diving.license_type') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('diving.technical_directors') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('diving.training_systems') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('diving.start_date') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('diving.expiration_date') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('diving.status') }}
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">{{ __('diving.actions') }}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($activeLicenses as $licenseAttributed)
                            @php
                                // Get all technical directors for this license
                                $technicalDirectors = $licenseAttributed->divingTechnicalDirectors()
                                    ->with('individual')
                                    ->where('status_class', 'Domain\\Diving\\States\\AssignedDivingTechnicalDirectorState')
                                    ->get();
                                
                                // Collect all unique certification systems
                                $allSystems = [];
                                foreach ($technicalDirectors as $director) {
                                    if (is_array($director->certification_systems)) {
                                        $allSystems = array_merge($allSystems, $director->certification_systems);
                                    }
                                }
                                $allSystems = array_unique($allSystems);
                            @endphp
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $licenseAttributed->license->name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($technicalDirectors->count() > 0)
                                        <div class="space-y-1">
                                            @foreach($technicalDirectors as $director)
                                                @if($director->individual)
                                                    <div class="text-sm text-gray-900">
                                                        {{ $director->individual->name }}
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-500">{{ __('diving.no_directors_assigned') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        {{ count($allSystems) > 0 ? implode(', ', $allSystems) : '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ $licenseAttributed->current_term_starts_at ? $licenseAttributed->current_term_starts_at->format('d/m/Y') : '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ $licenseAttributed->current_term_ends_at ? $licenseAttributed->current_term_ends_at->format('d/m/Y') : '-' }}
                                    </div>
                                    @if($licenseAttributed->current_term_ends_at && $licenseAttributed->current_term_ends_at->diffInDays(now()) <= 30 && !$licenseAttributed->current_term_ends_at->isPast())
                                        <div class="text-xs text-yellow-600">
                                            {{ __('diving.expiring_soon') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusConfig = [
                                            'Domain\\Licenses\\States\\ActiveLicenseAttributedState' => ['label' => __('diving.status_active'), 'bg' => 'bg-green-100', 'text' => 'text-green-800'],
                                            'Domain\\Licenses\\States\\PendingLicenseAttributedState' => ['label' => __('diving.status_pending'), 'bg' => 'bg-yellow-100', 'text' => 'text-yellow-800'],
                                            'Domain\\Licenses\\States\\CanceledLicenseAttributedState' => ['label' => __('diving.rejected_by_dt'), 'bg' => 'bg-red-100', 'text' => 'text-red-800'],
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
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('entity.diving_licenses.show', $licenseAttributed) }}"
                                       class="text-blue-600 hover:text-blue-800">
                                        {{ __('diving.view_details') }}
                                    </a>
                                    @if($licenseAttributed->status_class === 'Domain\\Licenses\\States\\ActiveLicenseAttributedState')
                                        <a href="{{ route('entity.diving_licenses.pdf', $licenseAttributed) }}"
                                           class="ml-3 text-blue-600 hover:text-blue-800"
                                           title="{{ __('diving.download_pdf') }}">
                                            <svg class="inline-block w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            {{ __('diving.pdf') }}
                                        </a>
                                    @endif
                                    @if($technicalDirectors->count() == 0)
                                        <a href="{{ route('entity.diving_licenses.invite_director', $licenseAttributed) }}"
                                           class="ml-3 text-blue-600 hover:text-blue-800">
                                            {{ __('diving.invite_director') }}
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                    {{ __('diving.no_licenses_found') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($activeLicenses->hasPages())
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $activeLicenses->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Pending Technical Director Invitations -->
    @if($pendingInvitations->count() > 0)
        <div class="mt-8">
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        {{ __('diving.pending_director_invitations') }}
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        {{ __('diving.invitations_sent_professionals') }}
                    </p>
                </div>
                <div class="border-t border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('diving.license') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('diving.invited_professional') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('diving.certification_system') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('diving.sent_on') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('diving.status') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($pendingInvitations as $invitation)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $invitation->license->name }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $invitation->individual ? $invitation->individual->name : '-' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ is_array($invitation->certification_systems) ? implode(', ', $invitation->certification_systems) : '-' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $invitation->created_at ? $invitation->created_at->format('d/m/Y') : '-' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            {{ __('diving.pending') }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</x-layout>