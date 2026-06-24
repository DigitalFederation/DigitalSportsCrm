@props(['individual'])

@php
    $assignments = $individual->divingTechnicalDirectorAssignments()
        ->where('status_class', 'Domain\Diving\States\AssignedDivingTechnicalDirectorState')
        ->with(['entity', 'licenseAttributed.license'])
        ->get();
@endphp

<div class="card">
    <div class="card-header">
        <h3 class="text-lg font-semibold">{{ __('diving.technical_director_positions') }}</h3>
    </div>
    <div class="card-body">
        @if($assignments->count() > 0)
            <div class="overflow-x-auto">
                <table class="table-auto w-full">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('diving.entity') }}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('diving.license_type') }}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('diving.certification_systems') }}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('diving.assigned_on') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($assignments as $assignment)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $assignment->entity->name }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                    {{ $assignment->licenseAttributed->license->name ?? '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                    {{ implode(', ', $assignment->certification_systems ?? []) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                    {{ $assignment->assigned_at ? $assignment->assigned_at->format('d/m/Y') : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500 text-sm">{{ __('diving.no_technical_director_positions') }}</p>
        @endif
    </div>
</div>