@php
    $routePrefix = ($routeNamespace ?? 'admin') . '.' . $holderType . '_diving_license_validation';
@endphp
@section('title', $pageTitle ?? __('diving.license_validation'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-5 mt-5 flex justify-between">
            <div>
                <h1 class="page-first-title">{{ $pageTitle ?? __('diving.license_validation') }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ $pageSubtitle ?? __('diving.all_diving_licenses') }}</p>
            </div>
        </div>

        <!-- Workflow Info Banner (Entity only) -->
        @if($holderType === 'entity')
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="text-sm text-blue-800">
                    <p class="font-medium mb-1">{{ __('diving.workflow_info_title') }}</p>
                    <div class="flex flex-wrap items-center gap-1 text-xs">
                        <span class="px-2 py-0.5 bg-orange-100 text-orange-800 rounded-full">{{ __('diving.status_waiting_dt_approval') }}</span>
                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        <span class="px-2 py-0.5 bg-amber-100 text-amber-800 rounded-full">{{ __('diving.status_pending_validation') }}</span>
                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        <span class="px-2 py-0.5 bg-green-100 text-green-800 rounded-full">{{ __('diving.status_active') }}</span>
                        <span class="text-blue-600 mx-1">{{ __('common.or') }}</span>
                        <span class="px-2 py-0.5 bg-red-100 text-red-800 rounded-full">{{ __('diving.status_rejected') }}</span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Filters -->
        <x-filter-form :post="route($routePrefix . '.index')">
            <x-forms.filter-input-text label="{{ __('common.name') }}" name="name" wrapperClass="flex-1 min-w-[180px]" />
            @if($holderType === 'entity')
                <x-forms.filter-input-select label="{{ __('diving.filter_entity') }}" name="entity" :options="$entities" wrapperClass="flex-1 min-w-[180px]" />
            @endif
            <x-forms.filter-input-select label="{{ __('diving.filter_license_type') }}" name="license" :options="$divingLicenses" wrapperClass="flex-1 min-w-[180px]" />
            <x-forms.filter-input-select label="{{ __('diving.filter_status') }}" name="status" :options="$statusOptions" wrapperClass="flex-1 min-w-[180px]" />
            @if($holderType === 'entity')
                <x-forms.filter-input-text label="{{ __('diving.filter_member_number') }}" name="member_number" wrapperClass="flex-1 min-w-[180px]" />
            @endif
        </x-filter-form>

        <!-- Licenses Table -->
        <div class="card-no-padding">
            <div class="overflow-x-auto">
                <table class="table-auto w-full divide-y divide-slate-200">
                    <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-slate-200">
                        <tr>
                            <th scope="col" class="px-2 first:pl-5 last:pr-5 py-2 whitespace-nowrap text-left">
                                {{ $holderType === 'entity' ? __('diving.entity') : __('diving.individual') }}
                            </th>
                            <th scope="col" class="px-2 first:pl-5 last:pr-5 py-2 whitespace-nowrap text-left">
                                {{ __('diving.license_type') }}
                            </th>
                            <th scope="col" class="px-2 first:pl-5 last:pr-5 py-2 whitespace-nowrap text-left">
                                {{ __('diving.status') }}
                            </th>
                            <th scope="col" class="px-2 first:pl-5 last:pr-5 py-2 whitespace-nowrap text-left">
                                {{ __('diving.submitted_on') }}
                            </th>
                            <th scope="col" class="px-2 first:pl-5 last:pr-5 py-2 whitespace-nowrap text-left">
                                {{ __('diving.value') }}
                            </th>
                            <th scope="col" class="relative px-2 first:pl-5 last:pr-5 py-2 text-right">
                                <span class="sr-only">{{ __('diving.actions') }}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-200">
                        @forelse ($licenses as $license)
                            <tr class="table-row">
                                <td class="px-2 first:pl-5 last:pr-5 py-2 whitespace-nowrap">
                                    <div class="font-medium text-slate-800">
                                        {{ $license->holder_name }}
                                    </div>
                                    <div class="text-xs text-slate-500">
                                        {{ $license->owner->email ?? '' }}
                                    </div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-2 whitespace-nowrap">
                                    <div class="text-slate-800">
                                        {{ $license->license_name }}
                                    </div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-2 whitespace-nowrap">
                                    @php
                                        $statusConfig = [
                                            'Domain\Licenses\States\ActiveLicenseAttributedState' => ['label' => __('diving.status_active'), 'bg' => 'bg-green-100', 'text' => 'text-green-800'],
                                            'Domain\Licenses\States\PendingLicenseAttributedState' => ['label' => __('diving.status_pending'), 'bg' => 'bg-yellow-100', 'text' => 'text-yellow-800'],
                                            'Domain\Licenses\States\CanceledLicenseAttributedState' => ['label' => __('diving.status_rejected'), 'bg' => 'bg-red-100', 'text' => 'text-red-800'],
                                            'Domain\Licenses\States\PendingValidationLicenseAttributedState' => ['label' => __('diving.status_pending_validation'), 'bg' => 'bg-amber-100', 'text' => 'text-amber-800'],
                                            'Domain\Licenses\States\PendingTechnicalDirectorApprovalLicenseAttributedState' => ['label' => __('diving.status_waiting_dt_approval'), 'bg' => 'bg-orange-100', 'text' => 'text-orange-800'],
                                            'Domain\Licenses\States\ProvisionalLicenseAttributedState' => ['label' => __('diving.status_provisional'), 'bg' => 'bg-blue-100', 'text' => 'text-blue-800'],
                                            'Domain\Licenses\States\WaitingApprovalLicenseAttributedState' => ['label' => __('diving.status_waiting_approval'), 'bg' => 'bg-indigo-100', 'text' => 'text-indigo-800'],
                                            'Domain\Licenses\States\ExpiredLicenseAttributedState' => ['label' => __('diving.status_expired'), 'bg' => 'bg-red-100', 'text' => 'text-red-800'],
                                            'Domain\Licenses\States\SuspendedLicenseAttributedState' => ['label' => __('diving.status_suspended'), 'bg' => 'bg-gray-100', 'text' => 'text-gray-800'],
                                        ];
                                        $status = $statusConfig[$license->status_class] ?? ['label' => __('diving.status_unknown'), 'bg' => 'bg-gray-100', 'text' => 'text-gray-800'];
                                    @endphp
                                    <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full {{ $status['bg'] }} {{ $status['text'] }}">
                                        {{ $status['label'] }}
                                    </span>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-2 whitespace-nowrap">
                                    <div class="text-slate-800">{{ $license->created_at->format('d/m/Y') }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-2 whitespace-nowrap">
                                    @if($license->total_value > 0)
                                        <span class="text-slate-800">{{ money($license->total_value) }}</span>
                                    @else
                                        <span class="text-emerald-600 font-medium">{{ __('diving.free') }}</span>
                                    @endif
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-2 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="space-x-1 flex justify-end items-end">
                                        <x-dynamic-table-buttons type="show" :route="route($routePrefix . '.show', $license)" />
                                        <x-dynamic-table-buttons type="document.pdf" :route="route($routePrefix . '.pdf', $license)" />
                                        <x-dynamic-table-buttons type="delete" :route="route($routePrefix . '.destroy', $license)" method="DELETE" />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-2 first:pl-5 last:pr-5 py-12 text-center">
                                    <div class="text-slate-500">{{ __('diving.no_diving_licenses') }}</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($licenses->hasPages())
                <div class="bg-white px-4 py-3 border-t border-slate-200 sm:px-6">
                    {{ $licenses->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layout>
