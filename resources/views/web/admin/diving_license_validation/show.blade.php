@php
    $routePrefix = ($routeNamespace ?? 'admin') . '.' . $holderType . '_diving_license_validation';
@endphp
@section('title', __('diving.review_license_request'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-3 mt-5">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('diving.review_license_request') }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ __('diving.license_validation_details') }}</p>
            </div>
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a href="{{ route($routePrefix . '.index') }}" class="btn btn-secondary">
                    {{ __('common.back') }}
                </a>
            </div>
        </div>

        @php
            $dtTotal = $holderType === 'entity' ? $licenseAttributed->divingTechnicalDirectors->count() : 0;
            $dtApproved = $holderType === 'entity' ? $licenseAttributed->divingTechnicalDirectors->filter(fn($d) => method_exists($d, 'hasApproved') ? $d->hasApproved() : false)->count() : 0;
            $requiresPayment = $licenseAttributed->total_value > 0;
            $statusConfigSticky = [
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
            $statusSticky = $statusConfigSticky[$licenseAttributed->status_class] ?? ['label' => __('diving.status_unknown'), 'bg' => 'bg-gray-100', 'text' => 'text-gray-800'];
        @endphp
        @if($holderType === 'entity')
        <div class="panel-box sticky top-16 z-20 bg-white border border-slate-200 rounded-lg mb-4 p-3">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-semibold text-slate-800 truncate">{{ $licenseAttributed->license_name }}</span>
                        <span class="text-slate-500">-</span>
                        <span class="text-sm text-slate-700 truncate">{{ $licenseAttributed->holder_name }}</span>
                        <span class="text-slate-500">-</span>
                        <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusSticky['bg'] }} {{ $statusSticky['text'] }}">
                            {{ $statusSticky['label'] }}
                        </span>
                        <span class="text-slate-500">-</span>
                        <span class="text-sm text-slate-700">
                            {{ __('diving.value') }}:
                            @if($requiresPayment)
                                <strong>{{ number_format($licenseAttributed->total_value, 2) }}EUR</strong>
                            @else
                                <span class="text-emerald-600 font-medium">{{ __('diving.free') }}</span>
                            @endif
                        </span>
                        <span class="text-slate-500">-</span>
                        <span class="text-sm text-slate-700">{{ __('diving.submitted_on') }} {{ $licenseAttributed->created_at->format('d/m/Y') }}</span>
                        @if($dtTotal > 0)
                            <span class="text-slate-500">-</span>
                            <span class="text-sm text-slate-700">DT {{ $dtApproved }}/{{ $dtTotal }}</span>
                        @endif
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if($licenseAttributed->status_class === \Domain\Licenses\States\PendingValidationLicenseAttributedState::class)
                        <a href="#actions" class="btn btn-success btn-sm">{{ __('diving.approve') }}</a>
                        <a href="#actions" class="btn btn-danger btn-sm">{{ __('diving.reject') }}</a>
                    @endif
                </div>
            </div>
        </div>
        @endif

        @if($holderType === 'individual')
            {{-- ============================================ --}}
            {{-- INDIVIDUAL LAYOUT: Profile Hero + 2-col grid --}}
            {{-- ============================================ --}}

            <!-- Individual Profile Hero -->
            <x-individual.profile-hero :individual="$licenseAttributed->owner" />

            <!-- Supplementary Info: Gender, Email, Phone -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 -mt-3 mb-6">
                <div class="px-4 sm:px-6 py-3 sm:py-4">
                    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-x-6 gap-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('main.gender') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @if($licenseAttributed->owner->gender === 'male')
                                    {{ __('main.male') }}
                                @elseif($licenseAttributed->owner->gender === 'female')
                                    {{ __('main.female') }}
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('main.email') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $licenseAttributed->owner->email ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('main.phone') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $licenseAttributed->owner->phone ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- 2-column grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left: License Information -->
                <div class="lg:col-span-2">
                    <div class="panel-box p-4">
                        <h3 class="grow font-semibold text-slate-800 truncate mb-3">{{ __('diving.license_information') }}</h3>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2">
                            <div class="min-w-0">
                                <dt class="text-xs text-slate-500">{{ __('diving.license_type') }}</dt>
                                <dd class="text-sm font-medium text-slate-800 truncate">{{ $licenseAttributed->license_name }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-slate-500">{{ __('diving.submitted_on') }}</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $licenseAttributed->created_at->format('d/m/Y H:i') }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-slate-500">{{ __('diving.issue_date') }}</dt>
                                <dd class="text-sm font-medium text-slate-800">{{ $licenseAttributed->activated_at?->format('d/m/Y') ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-slate-500">{{ __('diving.fee') }}</dt>
                                <dd class="text-sm font-medium text-slate-800">
                                    @if($licenseAttributed->total_value > 0)
                                        {{ number_format($licenseAttributed->total_value, 2) }}EUR
                                    @else
                                        <span class="text-emerald-600">{{ __('diving.free') }}</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs text-slate-500">{{ __('diving.status') }}</dt>
                                <dd class="text-sm font-medium">
                                    <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusSticky['bg'] }} {{ $statusSticky['text'] }}">
                                        {{ $statusSticky['label'] }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Right: Actions Sidebar -->
                <div id="actions" class="lg:col-span-1">
                    <div class="card">
                        <h3 class="text-lg font-semibold mb-4">{{ __('common.actions') }}</h3>

                        @if($licenseAttributed->status_class === 'Domain\Licenses\States\PendingValidationLicenseAttributedState')
                            <!-- Approve Form -->
                            <form action="{{ route($routePrefix . '.approve', $licenseAttributed) }}"
                                  method="POST"
                                  class="mb-3">
                                @csrf
                                <div class="mb-3">
                                    <label for="approve_notes" class="block text-sm font-medium mb-1">
                                        {{ __('diving.approval_notes') }}
                                    </label>
                                    <textarea id="approve_notes"
                                              name="notes"
                                              rows="2"
                                              class="form-textarea w-full"
                                              placeholder="{{ __('diving.optional_approval_notes') }}"></textarea>
                                </div>
                                <button type="submit"
                                        class="btn btn-success w-full"
                                        onclick="return confirm('{{ __('diving.confirm_approve_license') }}')">
                                    {{ __('diving.approve') }}
                                </button>
                            </form>

                            <!-- Reject Form -->
                            <form action="{{ route($routePrefix . '.reject', $licenseAttributed) }}"
                                  method="POST"
                                  class="mb-3">
                                @csrf
                                <div class="mb-3">
                                    <label for="reject_reason" class="block text-sm font-medium mb-1">
                                        {{ __('diving.rejection_reason') }} <span class="text-rose-500">*</span>
                                    </label>
                                    <textarea id="reject_reason"
                                              name="reason"
                                              rows="2"
                                              class="form-textarea w-full @error('reason') border-rose-300 @enderror"
                                              placeholder="{{ __('diving.rejection_reason_placeholder') }}"
                                              required>{{ old('reason') }}</textarea>
                                    @error('reason')
                                        <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit"
                                        class="btn btn-danger w-full"
                                        onclick="return confirm('{{ __('diving.confirm_reject_license') }}')">
                                    {{ __('diving.reject') }}
                                </button>
                            </form>
                        @else
                            <p class="text-slate-600 mb-4">{{ __('diving.license_not_pending_validation_message') }}</p>
                            <div>
                                <p class="text-sm text-slate-600 mb-2">{{ __('diving.current_status') }}:</p>
                                @php
                                    $statusConfig = [
                                        'Domain\Licenses\States\ActiveLicenseAttributedState' => ['label' => __('diving.status_active'), 'bg' => 'bg-green-100', 'text' => 'text-green-800'],
                                        'Domain\Licenses\States\PendingLicenseAttributedState' => ['label' => __('diving.status_pending'), 'bg' => 'bg-yellow-100', 'text' => 'text-yellow-800'],
                                        'Domain\Licenses\States\CanceledLicenseAttributedState' => ['label' => __('diving.rejected_by_dt'), 'bg' => 'bg-gray-100', 'text' => 'text-gray-800'],
                                        'Domain\Licenses\States\PendingValidationLicenseAttributedState' => ['label' => __('diving.status_pending_validation'), 'bg' => 'bg-amber-100', 'text' => 'text-amber-800'],
                                        'Domain\Licenses\States\PendingTechnicalDirectorApprovalLicenseAttributedState' => ['label' => __('diving.status_waiting_dt_approval'), 'bg' => 'bg-orange-100', 'text' => 'text-orange-800'],
                                        'Domain\Licenses\States\ProvisionalLicenseAttributedState' => ['label' => __('diving.status_provisional'), 'bg' => 'bg-blue-100', 'text' => 'text-blue-800'],
                                        'Domain\Licenses\States\WaitingApprovalLicenseAttributedState' => ['label' => __('diving.status_waiting_approval'), 'bg' => 'bg-indigo-100', 'text' => 'text-indigo-800'],
                                        'Domain\Licenses\States\ExpiredLicenseAttributedState' => ['label' => __('diving.status_expired'), 'bg' => 'bg-red-100', 'text' => 'text-red-800'],
                                        'Domain\Licenses\States\SuspendedLicenseAttributedState' => ['label' => __('diving.status_suspended'), 'bg' => 'bg-gray-100', 'text' => 'text-gray-800'],
                                    ];
                                    $status = $statusConfig[$licenseAttributed->status_class] ?? ['label' => __('diving.status_unknown'), 'bg' => 'bg-gray-100', 'text' => 'text-gray-800'];
                                @endphp
                                <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $status['bg'] }} {{ $status['text'] }}">
                                    {{ $status['label'] }}
                                </span>
                            </div>
                        @endif

                        <!-- Separator -->
                        <hr class="my-4 border-gray-200">

                        <!-- Danger Zone -->
                        <div class="p-3 bg-red-50 rounded-md">
                            <p class="text-xs text-red-600 mb-2">{{ __('diving.delete_license_warning') }}</p>
                            <form action="{{ route($routePrefix . '.destroy', $licenseAttributed) }}"
                                  method="POST"
                                  onsubmit="return confirm('{{ __('diving.confirm_delete_license') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-full">
                                    {{ __('diving.delete_license') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        @else
            {{-- ========================================= --}}
            {{-- ENTITY LAYOUT: Original flat layout       --}}
            {{-- ========================================= --}}

            <!-- License Information (compact) -->
            <div class="panel-box p-4 mb-6">
                <h3 class="grow font-semibold text-slate-800 truncate mb-3">{{ __('diving.license_information') }}</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-2">
                    <div class="min-w-0">
                        <dt class="text-xs text-slate-500">{{ __('diving.license_type') }}</dt>
                        <dd class="text-sm font-medium text-slate-800 truncate">{{ $licenseAttributed->license_name }}</dd>
                    </div>
                    <div class="min-w-0">
                        <dt class="text-xs text-slate-500">{{ __('diving.entity') }}</dt>
                        <dd class="text-sm font-medium text-slate-800 truncate">{{ $licenseAttributed->holder_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('diving.submitted_on') }}</dt>
                        <dd class="text-sm font-medium text-slate-800">{{ $licenseAttributed->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-slate-500">{{ __('diving.value') }}</dt>
                        <dd class="text-sm font-medium text-slate-800">
                            @if($licenseAttributed->total_value > 0)
                                {{ number_format($licenseAttributed->total_value, 2) }}EUR
                            @else
                                <span class="text-emerald-600">{{ __('diving.free') }}</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Technical Directors (compact) - Only for Entity -->
            <div class="card-no-padding mb-8">
                <div class="px-5 py-2.5 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="grow font-semibold text-slate-800 truncate">{{ __('diving.technical_directors') }}</h3>
                    @if($dtTotal > 0)
                        <span class="text-xs text-slate-600">{{ __('diving.status') }}: {{ $dtApproved }}/{{ $dtTotal }} {{ __('diving.approved') }}</span>
                    @endif
                </div>
                <div class="overflow-x-auto">
                    @if($licenseAttributed->divingTechnicalDirectors->count() > 0)
                        <table class="table-auto w-full divide-y divide-slate-200">
                            <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-slate-200">
                                <tr>
                                    <th class="px-2 first:pl-5 last:pr-5 py-2 whitespace-nowrap">
                                        <div class="font-semibold text-left">{{ __('diving.professional') }}</div>
                                    </th>
                                    <th class="px-2 first:pl-5 last:pr-5 py-2 whitespace-nowrap">
                                        <div class="font-semibold text-left">{{ __('diving.certification_systems') }}</div>
                                    </th>
                                    <th class="px-2 first:pl-5 last:pr-5 py-2 whitespace-nowrap">
                                        <div class="font-semibold text-left">{{ __('diving.status') }}</div>
                                    </th>
                                    <th class="px-2 first:pl-5 last:pr-5 py-2 whitespace-nowrap">
                                        <div class="font-semibold text-left">{{ __('diving.certifications') }}</div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="text-sm divide-y divide-slate-200">
                                @foreach($licenseAttributed->divingTechnicalDirectors as $assignment)
                                    <tr class="table-row">
                                        <td class="px-2 first:pl-5 last:pr-5 py-2 whitespace-nowrap">
                                            <div class="min-w-0">
                                                <div class="font-medium text-slate-800 truncate">{{ $assignment->individual->full_name }}</div>
                                                <div class="text-xs text-slate-500">{{ $assignment->individual->member_code }}</div>
                                            </div>
                                        </td>
                                        <td class="px-2 first:pl-5 last:pr-5 py-2 whitespace-nowrap">
                                            <div class="flex flex-wrap gap-1">
                                                @php
                                                    $systems = is_array($assignment->certification_systems) ? $assignment->certification_systems : (array) ($assignment->certification_systems ?? [$assignment->certification_system ?? null]);
                                                    $systems = array_filter($systems, fn($s) => filled($s));
                                                @endphp
                                                @forelse($systems as $sys)
                                                    <span class="px-2 py-0.5 text-xs rounded-full bg-slate-100 text-slate-700">{{ $sys }}</span>
                                                @empty
                                                    <span class="text-slate-500">-</span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td class="px-2 first:pl-5 last:pr-5 py-2 whitespace-nowrap">
                                            @php $approved = method_exists($assignment, 'hasApproved') ? $assignment->hasApproved() : false; @endphp
                                            <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full {{ $approved ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-800' }}">
                                                {{ $approved ? __('diving.approved') : __('diving.pending') }}
                                            </span>
                                        </td>
                                        <td class="px-2 first:pl-5 last:pr-5 py-2 whitespace-nowrap">
                                            @php $certs = $assignment->individual->divingProfessionalCertifications ?? collect(); @endphp
                                            <span class="px-2 py-0.5 text-xs rounded-full bg-slate-100 text-slate-700">{{ $certs->count() }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="p-4 text-sm text-slate-500">{{ __('diving.no_technical_directors') }}</div>
                    @endif
                </div>
            </div>

            <!-- Owner Information (compact) -->
            <div class="panel-box p-4 mb-6">
                <h3 class="grow font-semibold text-slate-800 truncate mb-3">
                    {{ __('diving.entity_information') }}
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <p class="text-sm text-slate-600">{{ __('common.name') }}</p>
                        <p class="font-medium text-slate-800">{{ $licenseAttributed->owner->name ?? $licenseAttributed->owner->full_name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-600">{{ __('common.email') }}</p>
                        <p class="font-medium text-slate-800">{{ $licenseAttributed->owner->email ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-600">{{ __('common.phone') }}</p>
                        <p class="font-medium text-slate-800">{{ $licenseAttributed->owner->phone ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-600">{{ __('common.address') }}</p>
                        <p class="font-medium text-slate-800">{{ $licenseAttributed->owner->address ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Validation Actions -->
            @if($licenseAttributed->status_class === 'Domain\Licenses\States\PendingValidationLicenseAttributedState')
            <div id="actions" class="panel-box p-4 mb-8">
                <h3 class="grow font-semibold text-slate-800 truncate mb-3">{{ __('common.actions') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Approve Form -->
                    <div class="panel-box p-4 bg-emerald-50">
                        <h4 class="font-medium text-slate-800 mb-2">{{ __('diving.approve_license') }}</h4>
                        <form action="{{ route($routePrefix . '.approve', $licenseAttributed) }}"
                              method="POST"
                              class="space-y-4">
                            @csrf
                            <div>
                                <label for="approve_notes" class="block text-sm font-medium mb-1">
                                    {{ __('diving.approval_notes') }}
                                </label>
                                <textarea id="approve_notes"
                                          name="notes"
                                          rows="2"
                                          class="form-textarea w-full"
                                          placeholder="{{ __('diving.optional_approval_notes') }}"></textarea>
                    </div>
                            <button type="submit"
                                    class="btn btn-success btn-sm"
                                    onclick="return confirm('{{ __('diving.confirm_approve_license') }}')">
                                {{ __('diving.approve') }}
                            </button>
                        </form>
                    </div>

                    <!-- Reject Form -->
                    <div class="panel-box p-4 bg-rose-50">
                        <h4 class="font-medium text-slate-800 mb-2">{{ __('diving.reject_license') }}</h4>
                        <form action="{{ route($routePrefix . '.reject', $licenseAttributed) }}"
                              method="POST"
                              class="space-y-4">
                            @csrf
                            <div>
                                <label for="reject_reason" class="block text-sm font-medium mb-1">
                                    {{ __('diving.rejection_reason') }} <span class="text-rose-500">*</span>
                                </label>
                                <textarea id="reject_reason"
                                          name="reason"
                                          rows="2"
                                          class="form-textarea w-full @error('reason') border-rose-300 @enderror"
                                          placeholder="{{ __('diving.rejection_reason_placeholder') }}"
                                          required>{{ old('reason') }}</textarea>
                                @error('reason')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('{{ __('diving.confirm_reject_license') }}')">
                                {{ __('diving.reject') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @else
            <div class="card mb-8">
                <h3 class="grow font-semibold text-slate-800 truncate mb-3">{{ __('diving.license_status') }}</h3>
                <p class="text-slate-600 mb-4">{{ __('diving.license_not_pending_validation_message') }}</p>
                <div>
                    <p class="text-sm text-slate-600 mb-2">{{ __('diving.current_status') }}:</p>
                    @php
                        $statusConfig = [
                            'Domain\Licenses\States\ActiveLicenseAttributedState' => ['label' => __('diving.status_active'), 'bg' => 'bg-green-100', 'text' => 'text-green-800'],
                            'Domain\Licenses\States\PendingLicenseAttributedState' => ['label' => __('diving.status_pending'), 'bg' => 'bg-yellow-100', 'text' => 'text-yellow-800'],
                            'Domain\Licenses\States\CanceledLicenseAttributedState' => ['label' => __('diving.rejected_by_dt'), 'bg' => 'bg-gray-100', 'text' => 'text-gray-800'],
                            'Domain\Licenses\States\PendingValidationLicenseAttributedState' => ['label' => __('diving.status_pending_validation'), 'bg' => 'bg-amber-100', 'text' => 'text-amber-800'],
                            'Domain\Licenses\States\PendingTechnicalDirectorApprovalLicenseAttributedState' => ['label' => __('diving.status_waiting_dt_approval'), 'bg' => 'bg-orange-100', 'text' => 'text-orange-800'],
                            'Domain\Licenses\States\ProvisionalLicenseAttributedState' => ['label' => __('diving.status_provisional'), 'bg' => 'bg-blue-100', 'text' => 'text-blue-800'],
                            'Domain\Licenses\States\WaitingApprovalLicenseAttributedState' => ['label' => __('diving.status_waiting_approval'), 'bg' => 'bg-indigo-100', 'text' => 'text-indigo-800'],
                            'Domain\Licenses\States\ExpiredLicenseAttributedState' => ['label' => __('diving.status_expired'), 'bg' => 'bg-red-100', 'text' => 'text-red-800'],
                            'Domain\Licenses\States\SuspendedLicenseAttributedState' => ['label' => __('diving.status_suspended'), 'bg' => 'bg-gray-100', 'text' => 'text-gray-800'],
                        ];
                        $status = $statusConfig[$licenseAttributed->status_class] ?? ['label' => __('diving.status_unknown'), 'bg' => 'bg-gray-100', 'text' => 'text-gray-800'];
                    @endphp
                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $status['bg'] }} {{ $status['text'] }}">
                        {{ $status['label'] }}
                    </span>
                </div>
            </div>
            @endif

            <!-- Delete License Section -->
            <div class="border border-rose-200 rounded-lg bg-rose-50 p-6">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">{{ __('diving.danger_zone') }}</h3>
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-medium text-slate-800">{{ __('diving.delete_license') }}</h4>
                        <p class="text-sm text-slate-600 mt-1">{{ __('diving.delete_license_warning') }}</p>
                    </div>
                    <form action="{{ route($routePrefix . '.destroy', $licenseAttributed) }}"
                          method="POST"
                          onsubmit="return confirm('{{ __('diving.confirm_delete_license') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            {{ __('diving.delete_license') }}
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-layout>
