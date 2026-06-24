@props(['individual'])

<div class="card">
    <div class="card-header">
        <h3 class="text-lg font-semibold">{{ __('diving.other_certifications') }}</h3>
    </div>
    <div class="card-body">
        @if($individual->divingProfessionalCertifications->count() > 0)
            <div class="overflow-x-auto">
                <table class="table-auto w-full">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('diving.certification') }}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('diving.system') }}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('diving.number') }}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('diving.issue_date') }}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('diving.status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($individual->divingProfessionalCertifications as $cert)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $cert->certification_name }}</div>
                                        @if($cert->certification_level)
                                            <div class="text-sm text-gray-500">{{ $cert->certification_level }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $cert->certification_system }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $cert->certification_number }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $cert->issue_date->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                                          style="background-color: {{ $cert->state->color() }}20; color: {{ $cert->state->color() }}">
                                        {{ $cert->state->name() }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500 text-sm">{{ __('diving.no_other_certifications') }}</p>
        @endif
    </div>
</div>