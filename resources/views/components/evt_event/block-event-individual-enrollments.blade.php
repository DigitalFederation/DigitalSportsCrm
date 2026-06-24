@if($federationIndividualEnrollments->isNotEmpty())
    @php
        $uniqueAttributes = $federationIndividualEnrollments->flatMap(function ($enrollment) {
            return $enrollment->individualEnrollments->flatMap(function ($individualEnrollment) {
                return $individualEnrollment->processedAttributes->keys();
            });
        })->unique()->sort()->values();
    @endphp
    <div class="card w-full mb-4">
        <div class="flex justify-between items-center border-b border-gray-300 pb-2 mb-4">
            <div class="font-bold flex gap-x-2">
                <x-svg.ticket class="w-6 h-6"></x-svg.ticket>{{ __('events.enrollments') }}
            </div>
        </div>

        <x-dynamic-table :headers="[]">
            <thead>
            <tr>
                <th class="text-left font-bold text-slate-600 text-sm pl-2">{{ __('events.enrollment_date') }}</th>
                <th class="text-left font-bold text-slate-600 text-sm">{{ __('common.name') }}</th>
                <th class="text-left font-bold text-slate-600 text-sm">{{ __('certifications.member_code') }}</th>
                <th class="text-left font-bold text-slate-600 text-sm">{{ __('common.status') }}</th>
                @foreach($uniqueAttributes as $attributeName)
                    <th class="text-left font-bold text-slate-600 text-sm">{{ $attributeName }}</th>
                @endforeach

            </tr>
            </thead>
            <tbody>
            @foreach($federationIndividualEnrollments as $enrollment)
                @foreach($enrollment->individualEnrollments as $individualEnrollment)
                    <tr class="hover:bg-gray-100">
                        <td class="pl-2">{{ $enrollment->created_at->format('d/m/Y') }}</td>
                        <td class="">{{ $individualEnrollment->individual->full_name }}</td>
                        <td>{{ $individualEnrollment->individual->member_code }}</td>
                        <td>
                            <x-tables.badge :status="$individualEnrollment->stateName()"
                                            :color="$individualEnrollment->stateColor()" />
                        </td>
                        @foreach($uniqueAttributes as $attributeName)
                            @php
                                $attributeValue = $individualEnrollment->processedAttributes[$attributeName] ?? 'N/A';
                            @endphp
                            <td>{{ $attributeValue }}</td>
                        @endforeach

                    </tr>
                @endforeach
            @endforeach
            </tbody>
        </x-dynamic-table>
    </div>
@endif
