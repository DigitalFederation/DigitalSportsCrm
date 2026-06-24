<!-- Analytics Modal -->
<div 
@if($showAnalyticsModal) 
    class="fixed inset-0 z-50 overflow-y-auto" 
    aria-labelledby="analytics-modal-title" 
    role="dialog" 
    aria-modal="true"
@else
    class="hidden"
@endif>
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeAnalyticsModal"></div>
        
        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full">
            
            <!-- Modal header -->
            <div class="bg-white px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="analytics-modal-title">
                        {{ __('License Analytics & Insights') }}
                    </h3>
                    <div class="flex items-center space-x-2">
                        <!-- Date Range Selector -->
                        <select wire:model="analyticsDateRange" class="form-select text-sm">
                            <option value="30">{{ __('Last 30 days') }}</option>
                            <option value="90">{{ __('Last 3 months') }}</option>
                            <option value="180">{{ __('Last 6 months') }}</option>
                            <option value="365">{{ __('Last year') }}</option>
                        </select>
                        
                        <button wire:click="closeAnalyticsModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Modal content -->
            <div class="bg-white px-6 py-4 max-h-96 overflow-y-auto">
                
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <!-- Total Licenses -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-blue-900">{{ __('Total Licenses') }}</p>
                                <p class="text-2xl font-bold text-blue-600">{{ number_format($analyticsData['total_licenses'] ?? 0) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Revenue -->
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-900">{{ __('Total Revenue') }}</p>
                                <p class="text-2xl font-bold text-green-600">€{{ number_format($analyticsData['total_revenue'] ?? 0) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Active Rate -->
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-purple-900">{{ __('Active Rate') }}</p>
                                <p class="text-2xl font-bold text-purple-600">{{ number_format($analyticsData['active_rate'] ?? 0, 1) }}%</p>
                            </div>
                        </div>
                    </div>

                    <!-- Avg. Value -->
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-amber-900">{{ __('Avg. License Value') }}</p>
                                <p class="text-2xl font-bold text-amber-600">€{{ number_format($analyticsData['avg_license_value'] ?? 0) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- License Creation Trend -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-lg font-semibold text-gray-800 mb-3">{{ __('License Creation Trend') }}</h4>
                        <div class="h-48">
                            @if(isset($analyticsData['creation_trend']) && count($analyticsData['creation_trend']) > 0)
                                <canvas id="creationTrendChart" width="400" height="200"></canvas>
                            @else
                                <div class="flex items-center justify-center h-full text-gray-500">
                                    <p>{{ __('No trend data available') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- License Types Distribution -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-lg font-semibold text-gray-800 mb-3">{{ __('License Types Distribution') }}</h4>
                        <div class="h-48">
                            @if(isset($analyticsData['license_types']) && count($analyticsData['license_types']) > 0)
                                <canvas id="licenseTypesChart" width="400" height="200"></canvas>
                            @else
                                <div class="flex items-center justify-center h-full text-gray-500">
                                    <p>{{ __('No license type data available') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Detailed Tables -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Top Performing Licenses -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-lg font-semibold text-gray-800 mb-3">{{ __('Top Performing Licenses') }}</h4>
                        
                        @if(isset($analyticsData['top_licenses']) && count($analyticsData['top_licenses']) > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('License') }}</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Count') }}</th>
                                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Revenue') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($analyticsData['top_licenses'] as $license)
                                            <tr>
                                                <td class="px-3 py-2 text-sm text-gray-900">{{ $license['name'] }}</td>
                                                <td class="px-3 py-2 text-sm text-gray-900">{{ number_format($license['count']) }}</td>
                                                <td class="px-3 py-2 text-sm text-gray-900">€{{ number_format($license['revenue'] ?? 0) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-4">{{ __('No license performance data available') }}</p>
                        @endif
                    </div>

                    <!-- Status Breakdown -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-lg font-semibold text-gray-800 mb-3">{{ __('Status Breakdown') }}</h4>
                        
                        @if(isset($analyticsData['status_breakdown']) && count($analyticsData['status_breakdown']) > 0)
                            <div class="space-y-3">
                                @foreach($analyticsData['status_breakdown'] as $status => $data)
                                    <div class="flex items-center justify-between p-2 bg-white rounded border">
                                        <div class="flex items-center">
                                            <span class="inline-flex w-3 h-3 rounded-full mr-2
                                                {{ $status === 'active' ? 'bg-green-500' : '' }}
                                                {{ $status === 'pending' ? 'bg-yellow-500' : '' }}
                                                {{ $status === 'suspended' ? 'bg-red-500' : '' }}
                                                {{ $status === 'expired' ? 'bg-gray-500' : '' }}"></span>
                                            <span class="text-sm font-medium capitalize">{{ __($status) }}</span>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-bold">{{ number_format($data['count']) }}</div>
                                            <div class="text-xs text-gray-500">{{ number_format($data['percentage'], 1) }}%</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-4">{{ __('No status data available') }}</p>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Modal footer -->
            <div class="bg-gray-50 px-6 py-3 flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    {{ __('Last updated: :time', ['time' => now()->format('d/m/Y H:i')]) }}
                </div>
                
                <div class="flex space-x-3">
                    <button wire:click="exportAnalyticsData" class="btn btn-secondary btn-sm">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        {{ __('Export') }}
                    </button>
                    
                    <button wire:click="refreshAnalyticsData" class="btn btn-secondary btn-sm">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        {{ __('Refresh') }}
                    </button>
                    
                    <button wire:click="closeAnalyticsModal" class="btn btn-primary btn-sm">
                        {{ __('Close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Scripts -->
@if(isset($analyticsData['creation_trend']) && count($analyticsData['creation_trend']) > 0)
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const creationCtx = document.getElementById('creationTrendChart');
        if (creationCtx) {
            new Chart(creationCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: @json(array_keys($analyticsData['creation_trend'])),
                    datasets: [{
                        label: '{{ __("Licenses Created") }}',
                        data: @json(array_values($analyticsData['creation_trend'])),
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
    });
</script>
@endif

@if(isset($analyticsData['license_types']) && count($analyticsData['license_types']) > 0)
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typesCtx = document.getElementById('licenseTypesChart');
        if (typesCtx) {
            new Chart(typesCtx.getContext('2d'), {
                type: 'pie',
                data: {
                    labels: @json(array_keys($analyticsData['license_types'])),
                    datasets: [{
                        data: @json(array_values($analyticsData['license_types'])),
                        backgroundColor: [
                            '#3b82f6', '#10b981', '#f59e0b', '#ef4444', 
                            '#8b5cf6', '#06b6d4', '#84cc16', '#f97316'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                padding: 10
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endif