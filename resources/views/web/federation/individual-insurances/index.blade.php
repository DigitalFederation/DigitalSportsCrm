@section('title', __('federation.individual_insurances.title'))
<x-layout>
    <div class="previous-layout-classes">
        <!-- Page Header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5 mt-5">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('federation.individual_insurances.title') }}</h1>
            </div>
        </div>

        <!-- Filter and Card Total Section -->
        <div class="sm:flex flex-row gap-4">
            <x-utility.card-total title="{{ __('federation.individual_insurances.title') }}" :count="$insurances->total()"></x-utility.card-total>

            <!-- FILTER RESULTS -->
            <x-filter-form :post="route('federation.individual-insurances.index')">
                <div class="w-full md:w-1/3 lg:w-1/4">
                    <label class="block text-sm font-medium mb-1" for="filter_individual_name">{{ __('federation.member_name') }}</label>
                    <input type="text"
                           class="form-input w-full"
                           name="filter_individual_name"
                           id="filter_individual_name"
                           value="{{ request('filter_individual_name', '') }}">
                </div>
                <div class="w-full md:w-1/3 lg:w-1/4">
                    <label class="block text-sm font-medium mb-1" for="filter_entity_id">{{ __('federation.entity') }}</label>
                    <select class="form-select w-full"
                            name="filter_entity_id"
                            id="filter_entity_id">
                        <option value="">{{ __('common.all') }}</option>
                        @foreach($entitiesForFilter as $entity)
                            <option value="{{ $entity->id }}" {{ request('filter_entity_id') == $entity->id ? 'selected' : '' }}>{{ $entity->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full md:w-1/3 lg:w-1/4">
                    <label class="block text-sm font-medium mb-1" for="filter_insurance_plan_id">{{ __('federation.insurance_plan') }}</label>
                    <select class="form-select w-full"
                            name="filter_insurance_plan_id"
                            id="filter_insurance_plan_id">
                        <option value="">{{ __('common.all') }}</option>
                        @foreach($insurancePlansForFilter as $plan)
                            <option value="{{ $plan->id }}" {{ request('filter_insurance_plan_id') == $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full md:w-1/3 lg:w-1/4">
                    <label class="block text-sm font-medium mb-1" for="filter_status_class">{{ __('federation.insurance_status') }}</label>
                    <select class="form-select w-full"
                            name="filter_status_class"
                            id="filter_status_class">
                        <option value="">{{ __('common.all') }}</option>
                        <option value="Domain\Insurance\States\ActiveInsuranceState" {{ request('filter_status_class') === 'Domain\Insurance\States\ActiveInsuranceState' ? 'selected' : '' }}>{{ __('common.active') }}</option>
                        <option value="Domain\Insurance\States\InactiveInsuranceState" {{ request('filter_status_class') === 'Domain\Insurance\States\InactiveInsuranceState' ? 'selected' : '' }}>{{ __('common.inactive') }}</option>
                        <option value="Domain\Insurance\States\PendingPaymentInsuranceState" {{ request('filter_status_class') === 'Domain\Insurance\States\PendingPaymentInsuranceState' ? 'selected' : '' }}>{{ __('federation.pending_payment') }}</option>
                        <option value="Domain\Insurance\States\SuspendedInsuranceState" {{ request('filter_status_class') === 'Domain\Insurance\States\SuspendedInsuranceState' ? 'selected' : '' }}>{{ __('federation.suspended') }}</option>
                        <option value="Domain\Insurance\States\ExpiredInsuranceState" {{ request('filter_status_class') === 'Domain\Insurance\States\ExpiredInsuranceState' ? 'selected' : '' }}>{{ __('federation.expired') }}</option>
                    </select>
                </div>
                <x-forms.filter-input-date-range label="{{ __('federation.coverage_period') }}" nameStart="filter_start_date" nameEnd="filter_end_date" />
            </x-filter-form>
        </div>

        <!-- Insurances Table -->
        <x-dynamic-table :pagination="$insurances" paginationTitle="{{ __('main.insurances') }}" :headers="[
            __('federation.member'),
            __('main.insurance_plan'),
            __('federation.activation_date'),
            __('federation.expiration_date'),
            __('federation.value'),
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
                                @if($insurance->member)
                                    <x-secure-profile-image :individual="$insurance->member" size="thumb" class="h-8 w-8 rounded-full" />
                                @else
                                    <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                        <span class="text-sm font-medium text-blue-600">?</span>
                                    </div>
                                @endif
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-slate-800">
                                    {{ $insurance->member->full_name ?? 'N/A' }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    {{ $insurance->member->member_code ?? $insurance->member->code_internal ?? '' }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="truncate max-w-xs" title="{{ $insurance->insurancePlan->name }}">
                            {{ Str::limit($insurance->insurancePlan->name, 30) }}
                        </div>
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        {{ $insurance->start_date?->format('d/m/Y') ?? '-' }}
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        {{ $insurance->end_date?->format('d/m/Y') ?? '-' }}
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-right">
                        <div class="text-slate-800 font-medium">
                            {{ number_format($insurance->individual_fee ?? 0, 2, ',', '.') }} €
                        </div>
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        @if ($insurance->policy_number)
                            {{ $insurance->policy_number }}
                        @else
                            <span class="text-red-500">{{ __('main.missing') }}</span>
                        @endif
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        @if($insurance->requester_type)
                            <div class="text-sm">
                                @php
                                    $requesterType = is_array($insurance->requester_type) ? 'unknown' : $insurance->requester_type;
                                    $requesterTypeLabel = match($requesterType) {
                                        'Domain\Entities\Models\Entity', 'entity' => __('main.entity'),
                                        'Domain\Individuals\Models\Individual', 'individual' => __('main.individual'),
                                        'App\Models\User', 'App\\Models\\User' => __('main.user'),
                                        default => __('main.unknown')
                                    };
                                    // Ensure it's always a string, fallback to English if translation returns array
                                    if (is_array($requesterTypeLabel)) {
                                        $requesterTypeLabel = match($requesterType) {
                                            'Domain\Entities\Models\Entity', 'entity' => 'Entity',
                                            'Domain\Individuals\Models\Individual', 'individual' => 'Individual',
                                            'App\Models\User', 'App\\Models\\User' => 'User',
                                            default => 'Unknown'
                                        };
                                    }
                                @endphp
                                @if($insurance->requester && is_object($insurance->requester) && isset($insurance->requester->name))
                                    <div class="text-sm font-medium text-gray-900 truncate max-w-xs" title="{{ $insurance->requester->name }}">
                                        {{ Str::limit($insurance->requester->name, 20) }}
                                    </div>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </div>
                        @else
                            {{-- Empty cell when no requester --}}
                        @endif
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        @php
                            $statusClass = class_basename($insurance->status_class);
                            $statusColor = match($statusClass) {
                                'ActiveInsuranceState' => 'bg-emerald-100 text-emerald-600',
                                'PendingPaymentInsuranceState' => 'bg-yellow-100 text-yellow-600',
                                'InactiveInsuranceState' => 'bg-slate-100 text-slate-600',
                                'ExpiredInsuranceState' => 'bg-red-100 text-red-600',
                                'SuspendedInsuranceState' => 'bg-orange-100 text-orange-600',
                                default => 'bg-slate-100 text-slate-600'
                            };
                            $statusText = match($statusClass) {
                                'ActiveInsuranceState' => __('insurances.active'),
                                'PendingPaymentInsuranceState' => __('insurances.pending_payment'),
                                'InactiveInsuranceState' => __('insurances.inactive'),
                                'ExpiredInsuranceState' => __('insurances.expired'),
                                'SuspendedInsuranceState' => __('insurances.suspended'),
                                default => __('main.unknown')
                            };
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                            {{ $statusText }}
                        </span>
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="space-x-1 flex justify-end">
                            <x-dynamic-table-buttons type="show" :route="route('federation.individual-insurances.show', $insurance->id)" />
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-dynamic-table>

        <!-- Pagination -->
        @if($insurances->hasPages())
            <div class="mt-6">
                {{ $insurances->links() }}
            </div>
        @endif
    </div>
</x-layout>