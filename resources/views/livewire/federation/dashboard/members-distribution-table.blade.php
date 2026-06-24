<div class="overflow-x-auto">
    @if($districts->count() > 0)
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th scope="col" rowspan="2" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-r border-gray-200 dark:border-gray-600">
                        {{ __('dashboard.age_group') }}
                    </th>
                    @foreach($districts as $district)
                        <th scope="col" colspan="2" class="px-2 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-r border-gray-200 dark:border-gray-600">
                            {{ $district->name }}
                        </th>
                    @endforeach
                    <th scope="col" colspan="2" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-white uppercase tracking-wider bg-gray-100 dark:bg-gray-600">
                        {{ __('dashboard.total') }}
                    </th>
                </tr>
                <tr>
                    @foreach($districts as $district)
                        <th scope="col" class="px-2 py-1 text-center text-xs font-medium text-gray-500 dark:text-gray-400 border-r border-gray-200 dark:border-gray-600" title="{{ __('dashboard.registered') }}">
                            {{ __('dashboard.registered') }}
                        </th>
                        <th scope="col" class="px-2 py-1 text-center text-xs font-medium text-green-600 dark:text-green-400 border-r border-gray-200 dark:border-gray-600" title="{{ __('dashboard.affiliated') }}">
                            {{ __('dashboard.affiliated') }}
                        </th>
                    @endforeach
                    <th scope="col" class="px-2 py-1 text-center text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-600">
                        {{ __('dashboard.registered') }}
                    </th>
                    <th scope="col" class="px-2 py-1 text-center text-xs font-medium text-green-700 dark:text-green-300 bg-gray-100 dark:bg-gray-600">
                        {{ __('dashboard.affiliated') }}
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($ageGroups as $key => $label)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors {{ str_contains($key, 'female') ? 'bg-pink-50/30 dark:bg-pink-900/10' : 'bg-blue-50/30 dark:bg-blue-900/10' }}">
                        <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white border-r border-gray-200 dark:border-gray-600">
                            <div class="flex items-center gap-2">
                                @if(str_contains($key, 'female'))
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-pink-100 dark:bg-pink-900/50">
                                        <svg class="w-3 h-3 text-pink-600 dark:text-pink-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                                        </svg>
                                    </span>
                                @else
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-100 dark:bg-blue-900/50">
                                        <svg class="w-3 h-3 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                                        </svg>
                                    </span>
                                @endif
                                {{ $label }}
                            </div>
                        </td>
                        @foreach($districts as $district)
                            @php
                                $districtData = $distribution[$district->id] ?? $this->getEmptyRow();
                                $registered = $districtData[$key]['registered'] ?? 0;
                                $affiliated = $districtData[$key]['affiliated'] ?? 0;
                            @endphp
                            <td class="px-2 py-2 whitespace-nowrap text-center text-sm text-gray-700 dark:text-gray-300 border-r border-gray-200 dark:border-gray-600">
                                {{ $registered > 0 ? $registered : '-' }}
                            </td>
                            <td class="px-2 py-2 whitespace-nowrap text-center text-sm text-green-600 dark:text-green-400 border-r border-gray-200 dark:border-gray-600">
                                {{ $affiliated > 0 ? $affiliated : '-' }}
                            </td>
                        @endforeach
                        <td class="px-2 py-2 whitespace-nowrap text-center text-sm font-semibold text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-600">
                            {{ $totals[$key]['registered'] ?? 0 }}
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap text-center text-sm font-semibold text-green-700 dark:text-green-300 bg-gray-100 dark:bg-gray-600">
                            {{ $totals[$key]['affiliated'] ?? 0 }}
                        </td>
                    </tr>
                @endforeach

                {{-- Grand Totals Row --}}
                <tr class="bg-gray-100 dark:bg-gray-700 font-bold">
                    <td class="px-3 py-2 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white border-r border-gray-200 dark:border-gray-600">
                        {{ __('dashboard.total') }}
                    </td>
                    @foreach($districts as $district)
                        @php
                            $districtData = $distribution[$district->id] ?? [];
                            $districtRegTotal = 0;
                            $districtAffTotal = 0;
                            foreach ($ageGroups as $agKey => $agLabel) {
                                $districtRegTotal += $districtData[$agKey]['registered'] ?? 0;
                                $districtAffTotal += $districtData[$agKey]['affiliated'] ?? 0;
                            }
                        @endphp
                        <td class="px-2 py-2 whitespace-nowrap text-center text-sm font-bold text-gray-900 dark:text-white border-r border-gray-200 dark:border-gray-600">
                            {{ $districtRegTotal }}
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap text-center text-sm font-bold text-green-700 dark:text-green-300 border-r border-gray-200 dark:border-gray-600">
                            {{ $districtAffTotal }}
                        </td>
                    @endforeach
                    @php
                        $grandRegTotal = 0;
                        $grandAffTotal = 0;
                        foreach ($ageGroups as $agKey => $agLabel) {
                            $grandRegTotal += $totals[$agKey]['registered'] ?? 0;
                            $grandAffTotal += $totals[$agKey]['affiliated'] ?? 0;
                        }
                    @endphp
                    <td class="px-2 py-2 whitespace-nowrap text-center text-sm font-bold text-gray-900 dark:text-white bg-gray-200 dark:bg-gray-600">
                        {{ $grandRegTotal }}
                    </td>
                    <td class="px-2 py-2 whitespace-nowrap text-center text-sm font-bold text-green-700 dark:text-green-300 bg-gray-200 dark:bg-gray-600">
                        {{ $grandAffTotal }}
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="mt-3 px-3 flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
            <span class="flex items-center gap-1">
                <span class="font-medium">{{ __('dashboard.registered') }}:</span> {{ __('dashboard.members_registered_help') }}
            </span>
            <span class="flex items-center gap-1">
                <span class="font-medium text-green-600 dark:text-green-400">{{ __('dashboard.affiliated') }}:</span> {{ __('dashboard.members_affiliated_help') }}
            </span>
        </div>
    @else
        <div class="text-center py-12">
            <div class="mx-auto h-12 w-12 rounded-full bg-gray-50 dark:bg-gray-700 flex items-center justify-center">
                <svg class="h-6 w-6 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                </svg>
            </div>
            <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('dashboard.no_members_data') }}</h3>
        </div>
    @endif
</div>
