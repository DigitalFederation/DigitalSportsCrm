@section('title', __('diving.professional_certifications_management'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-5 mt-5 flex justify-between">
            <div>
                <h1 class="page-first-title">{{ __('diving.professional_certifications_management') }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ __('diving.manage_validate_professional_certifications') }}</p>
            </div>
        </div>

        <!-- Filters -->
        <x-filter-form :post="route('admin.diving_professional_certifications.index')">
            <x-forms.filter-input-text label="{{ __('diving.filter_search_name') }}" name="name" wrapperClass="flex-1 min-w-[180px]" />
            <x-forms.filter-input-select label="{{ __('diving.filter_status') }}" name="status" :options="$statusOptions" wrapperClass="flex-1 min-w-[180px]" />
            <x-forms.filter-input-select label="{{ __('diving.filter_system') }}" name="system" :options="$systemOptions" wrapperClass="flex-1 min-w-[180px]" />
        </x-filter-form>

        <!-- Certifications Table -->
        <div class="card-no-padding">
            <div class="overflow-x-auto">
                <table class="table-auto w-full divide-y divide-slate-200">
                    <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-slate-200">
                        <tr>
                            <th scope="col" class="px-2 first:pl-5 last:pr-5 py-2 whitespace-nowrap text-left">
                                {{ __('diving.professional') }}
                            </th>
                            <th scope="col" class="px-2 first:pl-5 last:pr-5 py-2 whitespace-nowrap text-left">
                                {{ __('diving.member_number') }}
                            </th>
                            <th scope="col" class="px-2 first:pl-5 last:pr-5 py-2 whitespace-nowrap text-left">
                                {{ __('diving.certification') }}
                            </th>
                            <th scope="col" class="px-2 first:pl-5 last:pr-5 py-2 whitespace-nowrap text-left">
                                {{ __('diving.system') }}
                            </th>
                            <th scope="col" class="px-2 first:pl-5 last:pr-5 py-2 whitespace-nowrap text-left">
                                {{ __('diving.number') }}
                            </th>
                            <th scope="col" class="px-2 first:pl-5 last:pr-5 py-2 whitespace-nowrap text-left">
                                {{ __('diving.issue_date') }}
                            </th>
                            <th scope="col" class="px-2 first:pl-5 last:pr-5 py-2 whitespace-nowrap text-left">
                                {{ __('diving.status') }}
                            </th>
                            <th scope="col" class="relative px-2 first:pl-5 last:pr-5 py-2 text-right">
                                <span class="sr-only">{{ __('main.actions') }}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-200">
                        @forelse ($certifications as $certification)
                            @php
                                $statusConfig = [
                                    'Domain\Diving\States\ActiveDivingCertificationState' => ['label' => __('diving.active'), 'bg' => 'bg-green-100', 'text' => 'text-green-800'],
                                    'Domain\Diving\States\PendingValidationDivingCertificationState' => ['label' => __('diving.pending_validation'), 'bg' => 'bg-amber-100', 'text' => 'text-amber-800'],
                                    'Domain\Diving\States\ExpiredDivingCertificationState' => ['label' => __('diving.expired'), 'bg' => 'bg-gray-100', 'text' => 'text-gray-800'],
                                    'Domain\Diving\States\RevokedDivingCertificationState' => ['label' => __('diving.revoked'), 'bg' => 'bg-red-100', 'text' => 'text-red-800'],
                                ];
                                $status = $statusConfig[$certification->status_class] ?? ['label' => __('diving.status_unknown'), 'bg' => 'bg-gray-100', 'text' => 'text-gray-800'];
                            @endphp
                            <tr class="table-row">
                                <td class="px-2 first:pl-5 last:pr-5 py-2 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            <x-secure-profile-image :individual="$certification->individual" size="thumb" class="h-8 w-8 rounded-full" />
                                        </div>
                                        <span class="font-medium text-slate-800">{{ $certification->individual->full_name }}</span>
                                    </div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-2 whitespace-nowrap">
                                    {{ $certification->individual->member_number }}
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-2 whitespace-nowrap">
                                    <div>
                                        <div class="font-medium text-slate-800">{{ $certification->certification_name }}</div>
                                        @php
                                            $levelKeys = [
                                                'diver_level_3' => 'diving.diver_level_3_dive_leader',
                                                'instructor_level_1' => 'diving.instructor_level_1',
                                                'instructor_level_2' => 'diving.instructor_level_2',
                                                'instructor_level_3' => 'diving.instructor_level_3',
                                                'first_aid_bls_oxygen' => 'diving.first_aid_bls_oxygen',
                                                'compressor_operator' => 'diving.compressor_operator',
                                            ];
                                        @endphp
                                        <div class="text-xs text-slate-500">
                                            {{ __($levelKeys[$certification->certification_level] ?? $certification->certification_level) }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-2 whitespace-nowrap">
                                    <div class="text-slate-800">{{ $certification->certification_system }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-2 whitespace-nowrap">
                                    <div class="text-slate-800">{{ $certification->certification_number }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-2 whitespace-nowrap">
                                    <div class="text-slate-800">{{ $certification->issue_date->format('d/m/Y') }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-2 whitespace-nowrap">
                                    <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full {{ $status['bg'] }} {{ $status['text'] }}">
                                        {{ $status['label'] }}
                                    </span>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-2 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="space-x-1 flex justify-end items-end">
                                        <x-dynamic-table-buttons type="show" :route="route('admin.diving_professional_certifications.show', $certification)" />
                                        @if($certification->getFirstMedia('certificate_documents'))
                                            <x-dynamic-table-buttons type="document.pdf" :route="route('admin.diving_professional_certifications.download_document', $certification)" />
                                        @endif
                                        <x-dynamic-table-buttons type="delete" :route="route('admin.diving_professional_certifications.destroy', $certification)" method="DELETE" />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-2 first:pl-5 last:pr-5 py-12 text-center">
                                    <div class="text-slate-500">{{ __('diving.no_certifications_found') }}</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($certifications->hasPages())
                <div class="bg-white px-4 py-3 border-t border-slate-200 sm:px-6">
                    {{ $certifications->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layout>
