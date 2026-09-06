<x-layout>
    <div class="previous-layout-classes">

        <div class="mb-8 flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('affiliations.title') }}</h1>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex">
                    <svg class="w-5 h-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex">
                    <svg class="w-5 h-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <x-information-box title="{{ __('affiliations.info_title') }}"
            body="{{ __('affiliations.info_body') }}" />

        <div class="sm:flex flex-row gap-4">
            <!-- FILTER RESULTS -->
            <x-filter-form :post="route('admin.affiliations.index')">
                <div class="w-full md:w-1/3 lg:w-1/4">
                    <label class="block text-sm font-medium mb-1" for="filter_member_type">{{ __('affiliations.member_type') }}</label>
                    <select class="form-select w-full"
                            name="filter_member_type"
                            id="filter_member_type">
                        <option value="">{{ __('All') }}</option>
                        <option value="individual" {{ request('filter_member_type') === 'individual' ? 'selected' : '' }}>{{ __('main.individual_type') }}</option>
                        <option value="entity" {{ request('filter_member_type') === 'entity' ? 'selected' : '' }}>{{ __('main.entity_type') }}</option>
                    </select>
                </div>
                <div class="w-full md:w-1/3 lg:w-1/4">
                    <label class="block text-sm font-medium mb-1" for="filter_status_class">{{ __('affiliations.status') }}</label>
                    <select class="form-select w-full"
                            name="filter_status_class"
                            id="filter_status_class">
                        <option value="">{{ __('All') }}</option>
                        <option value="Domain\Memberships\States\ActiveAffiliationState" {{ request('filter_status_class') === 'Domain\Memberships\States\ActiveAffiliationState' ? 'selected' : '' }}>{{ __('affiliations.statuses.active') }}</option>
                        <option value="Domain\Memberships\States\PendingPaymentAffiliationState" {{ request('filter_status_class') === 'Domain\Memberships\States\PendingPaymentAffiliationState' ? 'selected' : '' }}>{{ __('affiliations.statuses.pending_payment') }}</option>
                        <option value="Domain\Memberships\States\InactiveAffiliationState" {{ request('filter_status_class') === 'Domain\Memberships\States\InactiveAffiliationState' ? 'selected' : '' }}>{{ __('affiliations.statuses.inactive') }}</option>
                        <option value="Domain\Memberships\States\SuspendedAffiliationState" {{ request('filter_status_class') === 'Domain\Memberships\States\SuspendedAffiliationState' ? 'selected' : '' }}>{{ __('affiliations.statuses.suspended') }}</option>
                        <option value="Domain\Memberships\States\ExpiredAffiliationState" {{ request('filter_status_class') === 'Domain\Memberships\States\ExpiredAffiliationState' ? 'selected' : '' }}>{{ __('affiliations.statuses.expired') }}</option>
                    </select>
                </div>
                <div class="w-full md:w-1/3 lg:w-1/4">
                    <label class="block text-sm font-medium mb-1" for="filter_federation">{{ __('affiliations.federation') }}</label>
                    <select class="form-select w-full"
                            name="filter_federation"
                            id="filter_federation">
                        <option value="">{{ __('All') }}</option>
                        @foreach($federations as $federation)
                            <option value="{{ $federation->id }}" {{ request('filter_federation') == $federation->id ? 'selected' : '' }}>{{ $federation->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full md:w-1/3 lg:w-1/4">
                    <label class="block text-sm font-medium mb-1" for="filter_member_name">{{ __('affiliations.member_name') }}</label>
                    <input type="text"
                           class="form-input w-full"
                           name="filter_member_name"
                           id="filter_member_name"
                           value="{{ request('filter_member_name', '') }}"
                    >
                </div>
                <div class="w-full md:w-1/3 lg:w-1/4">
                    <label class="block text-sm font-medium mb-1" for="filter_member_number">{{ __('main.member_number') }}</label>
                    <input type="text"
                           class="form-input w-full"
                           name="filter_member_number"
                           id="filter_member_number"
                           value="{{ request('filter_member_number', '') }}"
                    >
                </div>
                <x-forms.filter-input-date-range
                    label="affiliations.start_date"
                    nameStart="filter_start_date"
                    nameEnd="filter_end_date"
                />
            </x-filter-form>
        </div>

        <x-dynamic-table :pagination="$affiliations" paginationTitle="{{ __('affiliations.title') }}"
            :headers="[
                __('affiliations.table.member'),
                __('main.member_number'),
                __('affiliations.table.type'),
                __('affiliations.table.federation'),
                __('affiliations.table.start_date'),
                __('affiliations.table.end_date'),
                __('affiliations.table.fee'),
                __('main.requested_by'),
                __('affiliations.table.status'),
                __('main.actions'),
            ]">
            @foreach ($affiliations as $affiliation)
                <tr>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-8 w-8">
                                @if($affiliation->member && $affiliation->member instanceof \Domain\Individuals\Models\Individual)
                                    <x-secure-profile-image :individual="$affiliation->member" size="thumb" class="h-8 w-8 rounded-full" />
                                @elseif($affiliation->member)
                                    <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                                        <span class="text-sm font-medium text-green-600">{{ mb_substr($affiliation->member->name ?? '?', 0, 1) }}</span>
                                    </div>
                                @else
                                    <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                        <span class="text-sm font-medium text-blue-600">?</span>
                                    </div>
                                @endif
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">
                                    @if($affiliation->member)
                                        @if($affiliation->member instanceof \Domain\Individuals\Models\Individual)
                                            {{ $affiliation->member->native_name ?? $affiliation->member->full_name }}
                                        @else
                                            {{ $affiliation->member->name }}
                                        @endif
                                    @else
                                        {{ __('affiliations.member_not_found') }}
                                    @endif
                                </div>
                                @if($affiliation->member && method_exists($affiliation->member, 'code_internal'))
                                    <div class="text-sm text-gray-500">
                                        {{ $affiliation->member->code_internal }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-sm text-gray-900">
                        {{ $affiliation->member->member_number ?? '-' }}
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        @if($affiliation->member_type === 'individual' || $affiliation->member_type === \Domain\Individuals\Models\Individual::class)
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
                        <div class="flex items-center">
                            @if($affiliation->federation)
                                <div class="flex items-center">
                                    @if($affiliation->federation->hasMedia('flag'))
                                        <img src="{{ $affiliation->federation->getFirstMediaUrl('flag', 'thumb') }}" 
                                             alt="{{ $affiliation->federation->name }}" 
                                             class="w-5 h-5 mr-2 rounded">
                                    @endif
                                    <span class="text-sm">{{ $affiliation->federation->name }}</span>
                                </div>
                            @else
                                <span class="text-sm text-gray-500">{{ __('affiliations.no_federation') }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-sm text-gray-900">
                        {{ $affiliation->start_date?->format('d/m/Y') ?? __('affiliations.no_date') }}
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-sm text-gray-900">
                        {{ $affiliation->end_date?->format('d/m/Y') ?? __('affiliations.no_date') }}
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-sm text-gray-900">
                        @if(($affiliation->member_type === 'individual' || $affiliation->member_type === \Domain\Individuals\Models\Individual::class) && $affiliation->individual_fee)
                            {{ money($affiliation->individual_fee) }}
                        @elseif(($affiliation->member_type === 'entity' || $affiliation->member_type === \Domain\Entities\Models\Entity::class) && $affiliation->entity_fee)
                            {{ money($affiliation->entity_fee) }}
                        @else
                            <span class="text-gray-500">-</span>
                        @endif
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        @if($affiliation->requester_type)
                            <div class="text-sm">
                                @php
                                    $requesterType = is_array($affiliation->requester_type) ? 'unknown' : $affiliation->requester_type;
                                    $requesterTypeLabel = match($requesterType) {
                                        \Domain\Entities\Models\Entity::class, 'entity' => __('main.entity'),
                                        \Domain\Individuals\Models\Individual::class, 'individual' => __('main.individual'),
                                        \App\Models\User::class, 'user' => __('main.user'),
                                        default => __('main.unknown')
                                    };
                                    if (is_array($requesterTypeLabel)) {
                                        $requesterTypeLabel = class_basename($requesterType);
                                    }
                                @endphp
                                @if($affiliation->requester && is_object($affiliation->requester) && isset($affiliation->requester->name))
                                    <div class="text-sm font-medium text-gray-900" title="{{ $requesterTypeLabel }} - {{ $affiliation->requester->name }}">
                                        {{ $affiliation->requester->name }}
                                    </div>
                                    <div class="text-xs text-gray-500">{{ $requesterTypeLabel }}</div>
                                @else
                                    <span class="font-medium text-sm">{{ $requesterTypeLabel }}</span>
                                @endif
                                @if($affiliation->request_type === 'entity_group')
                                    <span class="text-xs text-gray-500">({{ __('main.for_members') }})</span>
                                @endif
                            </div>
                        @else
                            {{-- Empty cell when no requester --}}
                        @endif
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"
                        x-data="{ submitting: false }">
                        <form action="{{ route('admin.affiliations.update-status', $affiliation->id) }}" method="POST" class="inline-flex"
                              x-ref="statusForm">
                            @csrf
                            @method('PATCH')
                            <select name="status"
                                    x-on:change="if(confirm('{{ __('affiliations.confirm_status_change') }}')) { submitting = true; $refs.statusForm.submit(); }"
                                    :disabled="submitting"
                                    class="text-sm rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50">
                                <option value="active" @if($affiliation->status_class === \Domain\Memberships\States\ActiveAffiliationState::class) selected @endif>
                                    {{ __('affiliations.statuses.active') }}
                                </option>
                                <option value="pending_payment" @if($affiliation->status_class === \Domain\Memberships\States\PendingPaymentAffiliationState::class) selected @endif>
                                    {{ __('affiliations.statuses.pending_payment') }}
                                </option>
                                <option value="inactive" @if($affiliation->status_class === \Domain\Memberships\States\InactiveAffiliationState::class) selected @endif>
                                    {{ __('affiliations.statuses.inactive') }}
                                </option>
                                <option value="suspended" @if($affiliation->status_class === \Domain\Memberships\States\SuspendedAffiliationState::class) selected @endif>
                                    {{ __('affiliations.statuses.suspended') }}
                                </option>
                                <option value="expired" @if($affiliation->status_class === \Domain\Memberships\States\ExpiredAffiliationState::class) selected @endif>
                                    {{ __('affiliations.statuses.expired') }}
                                </option>
                            </select>
                        </form>
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="space-x-1 flex justify-end items-end">
                            @if($affiliation->member)
                                @if($affiliation->member_type === 'individual' || $affiliation->member_type === \Domain\Individuals\Models\Individual::class)
                                    <x-dynamic-table-buttons type="show" :route="route('admin.individual.show', $affiliation->member->id)" />
                                @else
                                    <x-dynamic-table-buttons type="show" :route="route('admin.entity.show', $affiliation->member->id)" />
                                @endif
                            @endif
                            <x-dynamic-table-buttons type="delete"
                                                     :route="route('admin.affiliations.destroy', $affiliation)"
                                                     method="DELETE"
                                                     x-on:click.prevent="$dispatch('open-modal', { id: 'delete-affiliation-{{ $affiliation->id }}' })" />
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-dynamic-table>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $affiliations->links() }}
        </div>

        <!-- Delete Confirmation Modals -->
        @foreach ($affiliations as $affiliation)
            <x-modal name="delete-affiliation-{{ $affiliation->id }}" maxWidth="md">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        {{ __('affiliations.confirm_delete_title') }}
                    </h2>
                    
                    <p class="text-sm text-gray-600 mb-4">
                        {{ __('affiliations.confirm_delete_message') }}
                    </p>
                    
                    <div class="bg-gray-50 p-3 rounded-lg mb-4">
                        <p class="text-sm">
                            <span class="font-semibold">{{ __('affiliations.member') }}:</span> 
                            {{ $affiliation->member?->name ?? __('affiliations.member_not_found') }}
                        </p>
                        <p class="text-sm">
                            <span class="font-semibold">{{ __('affiliations.federation') }}:</span> 
                            {{ $affiliation->federation?->name ?? __('affiliations.no_federation') }}
                        </p>
                        @if($affiliation->start_date)
                            <p class="text-sm">
                                <span class="font-semibold">{{ __('affiliations.period') }}:</span> 
                                {{ $affiliation->start_date->format('d/m/Y') }} - {{ $affiliation->end_date?->format('d/m/Y') ?? __('affiliations.no_date') }}
                            </p>
                        @endif
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button"
                                @click="$dispatch('close-modal', { id: 'delete-affiliation-{{ $affiliation->id }}' })"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            {{ __('main.cancel') }}
                        </button>
                        
                        <form action="{{ route('admin.affiliations.destroy', $affiliation) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                {{ __('affiliations.delete_confirm') }}
                            </button>
                        </form>
                    </div>
                </div>
            </x-modal>
        @endforeach

    </div>
</x-layout>