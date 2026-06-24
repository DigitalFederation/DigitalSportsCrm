@php
$roles = $getRecord()->professionalRoleEntities;
@endphp

<div>
    @forelse($roles as $entityRole)
        <x-tables.badge :status="ucfirst($entityRole->stateName()) . ' (' . $entityRole->role_name . ')'" :color="$entityRole->stateColor()" />
    @empty
        <span class="text-xs font-medium text-gray-500">{{ __('Not Associated') }}</span>
    @endforelse
</div>
