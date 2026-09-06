<x-layout>
    <div class="previous-layout-classes">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('main.membership_packages') }}</h1>
            </div>
            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a class="btn btn-primary" href="{{ route('admin.membership-packages.create') }}">
                    <span>{{ __('main.create_membership_package') }}</span>
                </a>
            </div>
        </div>

        <x-information-box title="{{ __('main.membership_packages_info_title') }}"
            body="{{ __('main.membership_packages_info_body') }}" />

        <div class="sm:flex sm:justify-center sm:items-center mb-5">
            <!-- Table -->
            <x-dynamic-table :pagination="$packages" :headers="[
                __('main.name'),
                __('main.target_type'),
                __('main.status'),
                __('main.affiliation_plans'),
                __('main.insurances'),
                __('main.individual_price'),
                __('main.entity_price'),
                '',
            ]">
                @foreach ($packages as $package)
                    <tr>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">{{ $package->name }}</td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            {{ $package->target_type->label() }}
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            @if ($package->is_active)
                                <span class="text-green-600">{{ __('main.active') }}</span>
                            @else
                                <span class="text-red-600">{{ __('main.inactive') }}</span>
                            @endif
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            {{ $package->affiliationPlans->count() }}</td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            {{ $package->insurancePlans->count() }}</td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            @php
                                $individualPrice = $package->affiliationPlans->sum('individual_fee') + $package->insurancePlans->sum('premium');
                            @endphp
                            @if($individualPrice > 0)
                                {{ money($individualPrice) }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            @php
                                $entityPrice = $package->affiliationPlans->sum('entity_fee') + $package->insurancePlans->sum('premium');
                            @endphp
                            @if($entityPrice > 0)
                                {{ money($entityPrice) }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-right">
                            <div class="flex justify-end gap-x-2">
                                <x-dynamic-table-buttons type="show" :route="route('admin.membership-packages.show', $package->id)" />
                                <x-dynamic-table-buttons type="edit" :route="route('admin.membership-packages.edit', $package->id)" />
                                <x-dynamic-table-buttons method="DELETE" type="delete" :route="route('admin.membership-packages.delete', $package->id)" />
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-dynamic-table>
        </div>
        <!-- Pagination -->
        {{ $packages->links() }}
    </div>
</x-layout>
