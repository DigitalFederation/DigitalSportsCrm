<!-- resources/views/livewire/report-generator.blade.php -->
<div>
    <x-information-box
        :title="__('reports.generate_new')"
        :body="__('reports.generate_description')"
    >
    </x-information-box>


    <form wire:submit.prevent="generateReport" class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
        <div class="space-y-6">
            <!-- Report Type Selection -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="col-span-1 md:col-span-3">
                    <label for="reportType" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('reports.report_type') }}
                    </label>
                    <select
                        id="reportType"
                        wire:model="reportType"
                        class="form-select block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    >
                        @foreach($reportTypes as $type => $displayName)
                            <option value="{{ $type }}">{{ $displayName }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Range Selection -->
                <div>
                    <label for="startDate" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('reports.start_date') }}
                    </label>
                    <input
                        type="date"
                        id="startDate"
                        wire:model="startDate"
                        class="form-input block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    >
                </div>

                <div>
                    <label for="endDate" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('reports.end_date') }}
                    </label>
                    <input
                        type="date"
                        id="endDate"
                        wire:model="endDate"
                        class="form-input block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    >
                </div>

                <div class="flex items-end">
                    <button
                        type="submit"
                        class="w-full md:w-auto px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-md shadow-sm transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove class="flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            {{ __('reports.generate_report') }}
                        </span>
                        <span wire:loading class="flex items-center justify-center gap-2">
                            <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ __('reports.generating') }}
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </form>

    @if($generatingReport)
        <div class="mt-4">
            <p>{{ __('reports.generating_report') }}</p>
            <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 mt-2">
                <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-500 ease-in-out"
                     style="width: {{ $progress }}%" wire:poll="getReportStatus"></div>
            </div>
        </div>
    @endif


    <div class="mt-8">
        <x-dynamic-table
            :headers="[
                __('reports.table.report_type'),
                __('reports.table.generated_by'),
                __('reports.table.date_generated'),
                __('reports.table.date_range'),
                __('reports.table.size'),
                __('reports.table.status'),
                __('reports.table.actions')
            ]">
            @foreach($reports as $report)
                <tr>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>{{ $report->name }}</span>
                        </div>
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="flex items-center">
                            <img class="h-8 w-8 rounded-full mr-2" src="{{ $report->user->profile_photo_url }}" alt="">
                            <span>{{ $report->user->name }}</span>
                        </div>
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="flex flex-col">
                            <span class="text-sm font-medium text-gray-900">
                                {{ $report->generated_on->format('d/m/Y') }}
                            </span>
                            <span class="text-xs text-gray-500">
                                {{ $report->generated_on->format('H:i') }}
                            </span>
                        </div>
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="flex flex-col">
                            @if(isset($report->filters['start_date']) || isset($report->filters['end_date']))
                                <span class="text-sm">
                                    {{ isset($report->filters['start_date']) ? \Carbon\Carbon::parse($report->filters['start_date'])->format('d/m/Y') : __('reports.all_time') }}
                                    →
                                    {{ isset($report->filters['end_date']) ? \Carbon\Carbon::parse($report->filters['end_date'])->format('d/m/Y') : __('reports.present') }}
                                </span>
                            @else
                                <span class="text-sm text-gray-500">{{ __('reports.no_date_range') }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        @if($report->file_size)
                            <span class="text-sm text-gray-600">
                                {{ number_format($report->file_size / 1024, 2) }} KB
                            </span>
                        @endif
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($report->status === 'ready') bg-green-100 text-green-800
                            @elseif($report->status === 'processing') bg-yellow-100 text-yellow-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ __('reports.status.' . $report->status) }}
                        </span>
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-right">
                        <div class="space-x-1 flex items-center justify-end">
                            @if($report->status === 'ready')
                                <x-dynamic-table-buttons
                                    type="download"
                                    :route="route('admin.reports.download', $report)"
                                    :title="__('reports.download_report')" />
                            @endif
                            <x-dynamic-table-buttons
                                type="delete"
                                method="DELETE"
                                :route="route('admin.reports.delete', $report)"
                                :title="__('reports.delete_report')" />
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-dynamic-table>
    </div>

    <div class="mt-8">
        {{ $reports->links() }}
    </div>
</div>
