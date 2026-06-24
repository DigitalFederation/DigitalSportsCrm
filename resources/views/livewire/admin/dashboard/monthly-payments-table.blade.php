<div>
    <div class="flex items-center justify-end mb-3">
        <label for="year-selector" class="text-sm font-medium text-gray-700 dark:text-gray-300 mr-2">
            {{ __('dashboard.year') }}:
        </label>
        <select id="year-selector" wire:model.live="selectedYear"
            class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @foreach($availableYears as $year)
                <option value="{{ $year }}">{{ $year }}</option>
            @endforeach
        </select>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                    <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-r border-gray-200 dark:border-gray-600">
                        {{ __('dashboard.category') }}
                    </th>
                    @foreach($months as $monthNum => $monthLabel)
                        <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-r border-gray-200 dark:border-gray-600">
                            {{ $monthLabel }}
                        </th>
                    @endforeach
                    <th scope="col" class="px-2 py-2 text-center text-xs font-medium text-gray-900 dark:text-white uppercase tracking-wider bg-gray-100 dark:bg-gray-600">
                        {{ __('dashboard.total') }}
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($categories as $categoryKey => $categoryLabel)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white border-r border-gray-200 dark:border-gray-600">
                            {{ $categoryLabel }}
                        </td>
                        @foreach($months as $monthNum => $monthLabel)
                            @php $value = $monthlyData[$categoryKey][$monthNum] ?? 0; @endphp
                            <td class="px-2 py-2 whitespace-nowrap text-center text-sm text-gray-700 dark:text-gray-300 border-r border-gray-200 dark:border-gray-600">
                                {{ $value > 0 ? number_format($value, 2, ',', '.') : '-' }}
                            </td>
                        @endforeach
                        <td class="px-2 py-2 whitespace-nowrap text-center text-sm font-semibold text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-600">
                            {{ number_format($categoryTotals[$categoryKey] ?? 0, 2, ',', '.') }}
                        </td>
                    </tr>
                @endforeach

                {{-- Totals Row --}}
                <tr class="bg-gray-100 dark:bg-gray-700 font-bold">
                    <td class="px-3 py-2 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white border-r border-gray-200 dark:border-gray-600">
                        {{ __('dashboard.total') }}
                    </td>
                    @foreach($months as $monthNum => $monthLabel)
                        <td class="px-2 py-2 whitespace-nowrap text-center text-sm font-bold text-gray-900 dark:text-white border-r border-gray-200 dark:border-gray-600">
                            {{ ($monthTotals[$monthNum] ?? 0) > 0 ? number_format($monthTotals[$monthNum], 2, ',', '.') : '-' }}
                        </td>
                    @endforeach
                    <td class="px-2 py-2 whitespace-nowrap text-center text-sm font-bold text-gray-900 dark:text-white bg-gray-200 dark:bg-gray-600">
                        {{ number_format($grandTotal, 2, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="mt-3 px-3 text-xs text-gray-500 dark:text-gray-400">
        {{ __('dashboard.monthly_payments_desc') }}
    </div>
</div>
