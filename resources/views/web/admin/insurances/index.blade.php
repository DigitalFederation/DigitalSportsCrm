<x-layout>
    <div class="previous-layout-classes">

        <div class="mb-8 flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('main.insurances') }}</h1>
        </div>

        <x-information-box title="{{ __('main.insurances_info_title') }}" body="{{ __('main.insurances_info_body') }}" />

        <div class="sm:flex flex-row gap-4">
            <!-- FILTER RESULTS -->
            <x-filter-form :post="route('admin.insurances.index')">
                <x-forms.filter-input-select label="{{ __('main.member_type') }}" name="member_type"
                    :options="[
                        'individual' => __('main.individual_type'),
                        'entity' => __('main.entity_type'),
                    ]" />
                <x-forms.filter-input-text label="{{ __('main.member_name') }}" name="member.name" />
                <x-forms.filter-input-text label="{{ __('main.member_number') }}" name="member.member_number" />
                <x-forms.filter-input-select label="{{ __('main.insurance_plan') }}" name="insurance_plan_id"
                    :options="$insurancePlans->toArray()" />
                <x-forms.filter-input-select label="{{ __('main.status') }}" name="status_class"
                    :options="[
                        \Domain\Insurance\States\ActiveInsuranceState::class => __('insurances.active'),
                        \Domain\Insurance\States\PendingPaymentInsuranceState::class => __('insurances.pending_payment'),
                        \Domain\Insurance\States\InactiveInsuranceState::class => __('insurances.inactive'),
                        \Domain\Insurance\States\SuspendedInsuranceState::class => __('insurances.suspended'),
                        \Domain\Insurance\States\ExpiredInsuranceState::class => __('insurances.expired'),
                    ]" />
                <x-forms.filter-input-text label="{{ __('main.requested_by') }}" name="requester.name" />
                <x-forms.filter-input-date-range
                    label="main.activation_date_from"
                    nameStart="filter_activation_start"
                    nameEnd="filter_activation_end"
                />
            </x-filter-form>
        </div>

        <x-dynamic-table :pagination="$insurances" paginationTitle="{{ __('main.insurances') }}" :headers="[
            __('main.member'),
            __('main.member_number'),
            __('main.insurance_plan'),
            __('main.type'),
            __('main.created_at'),
            __('main.start_date'),
            __('main.end_date'),
            __('main.policy_number'),
            __('main.requested_by'),
            __('main.status'),
            __('main.actions'),
        ]">
            @foreach ($insurances as $insurance)
                <tr>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-8 w-8">
                                @if($insurance->member && ($insurance->member_type === 'individual' || $insurance->member_type === \Domain\Individuals\Models\Individual::class))
                                    <x-secure-profile-image :individual="$insurance->member" size="thumb" class="h-8 w-8 rounded-full" />
                                @elseif($insurance->member)
                                    <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                                        <span class="text-sm font-medium text-green-600">{{ mb_substr($insurance->member->name ?? '?', 0, 1) }}</span>
                                    </div>
                                @else
                                    <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                        <span class="text-sm font-medium text-blue-600">?</span>
                                    </div>
                                @endif
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">
                                    @if($insurance->member)
                                        @if($insurance->member_type === 'individual' || $insurance->member_type === \Domain\Individuals\Models\Individual::class)
                                            {{ $insurance->member->native_name ?? $insurance->member->full_name }}
                                        @else
                                            {{ $insurance->member->name }}
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-sm text-gray-900">
                        @if($insurance->member && ($insurance->member_type === 'individual' || $insurance->member_type === \Domain\Individuals\Models\Individual::class))
                            {{ $insurance->member->member_number ?? '-' }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="truncate max-w-xs" title="{{ $insurance->insurancePlan->name }}">
                            {{ Str::limit($insurance->insurancePlan->name, 30) }}
                        </div>
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        @if($insurance->member_type === 'individual' || $insurance->member_type === \Domain\Individuals\Models\Individual::class)
                            <x-ui.badge variant="blue" size="sm">
                                {{ __('main.individual_type') }}
                            </x-ui.badge>
                        @else
                            <x-ui.badge variant="green" size="sm">
                                {{ __('main.entity_type') }}
                            </x-ui.badge>
                        @endif
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        @if($insurance->status_class === \Domain\Insurance\States\PendingPaymentInsuranceState::class)
                            <span class="text-orange-600 font-medium">{{ __('main.pending_payment') }}</span>
                        @elseif($insurance->status_class === \Domain\Insurance\States\InactiveInsuranceState::class)
                            <span class="text-red-600 font-medium">{{ __('insurances.inactive') }}</span>
                        @else
                            {{ $insurance->created_at->format('Y-m-d') }}
                        @endif
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        {{ $insurance->start_date->format('Y-m-d') }}
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        {{ $insurance->end_date->format('Y-m-d') }}
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        @if ($insurance->policy_number)
                            {{ $insurance->policy_number }}
                        @else
                            <span class="text-red-500">{{ __('main.missing') }}</span>
                        @endif
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        @if($insurance->requester && is_object($insurance->requester) && isset($insurance->requester->name))
                            @php
                                $requesterType = is_array($insurance->requester_type) ? '' : $insurance->requester_type;
                                $requesterTypeLabel = match($requesterType) {
                                    \Domain\Entities\Models\Entity::class, 'entity' => __('main.entity'),
                                    \Domain\Individuals\Models\Individual::class, 'individual' => __('main.individual'),
                                    \App\Models\User::class, 'user' => __('main.user'),
                                    default => ''
                                };
                            @endphp
                            <div class="text-sm">
                                <div class="text-sm font-medium text-gray-900 truncate max-w-xs" title="{{ $insurance->requester->name }}">
                                    {{ Str::limit($insurance->requester->name, 20) }}
                                </div>
                                @if($requesterTypeLabel)
                                    <div class="text-xs text-gray-500">{{ $requesterTypeLabel }}</div>
                                @endif
                            </div>
                        @endif
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <form action="{{ route('admin.insurances.update-status', $insurance->id) }}" method="POST" class="inline-flex">
                            @csrf
                            @method('PATCH')
                            <select name="status_class"
                                    onchange="this.form.submit()"
                                    class="text-sm rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="{{ \Domain\Insurance\States\ActiveInsuranceState::class }}" @if($insurance->status_class === \Domain\Insurance\States\ActiveInsuranceState::class) selected @endif>
                                    {{ __('insurances.active') }}
                                </option>
                                <option value="{{ \Domain\Insurance\States\PendingPaymentInsuranceState::class }}" @if($insurance->status_class === \Domain\Insurance\States\PendingPaymentInsuranceState::class) selected @endif>
                                    {{ __('insurances.pending_payment') }}
                                </option>
                                <option value="{{ \Domain\Insurance\States\InactiveInsuranceState::class }}" @if($insurance->status_class === \Domain\Insurance\States\InactiveInsuranceState::class) selected @endif>
                                    {{ __('insurances.inactive') }}
                                </option>
                                <option value="{{ \Domain\Insurance\States\SuspendedInsuranceState::class }}" @if($insurance->status_class === \Domain\Insurance\States\SuspendedInsuranceState::class) selected @endif>
                                    {{ __('insurances.suspended') }}
                                </option>
                                <option value="{{ \Domain\Insurance\States\ExpiredInsuranceState::class }}" @if($insurance->status_class === \Domain\Insurance\States\ExpiredInsuranceState::class) selected @endif>
                                    {{ __('insurances.expired') }}
                                </option>
                            </select>
                        </form>
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="space-x-1 flex justify-end">
                            <x-dynamic-table-buttons type="show" :route="route('admin.insurances.show', $insurance->id)" />
                            <x-dynamic-table-buttons type="edit" :route="route('admin.insurances.edit', $insurance->id)" />
                            @if ($insurance->member_type === \Domain\Individuals\Models\Individual::class)
                                <a href="{{ route('admin.insurances.document.download', $insurance->id) }}"
                                   class="flex items-center justify-center" title="{{ __('main.download') }}">
                                    <span class="sr-only">{{ __('main.download') }}</span>
                                    <x-svg.box-arrow-down class="w-5 h-5" />
                                </a>
                            @endif
                            <x-dynamic-table-buttons
                                type="delete"
                                method="DELETE"
                                :route="route('admin.insurances.destroy', $insurance->id)"
                                confirmText="{{ __('insurances.confirm_delete') }}" />
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-dynamic-table>

        <div class="mt-4">
            {{ $insurances->links() }}
        </div>

    </div>
</x-layout>
