<div class="space-y-6">
    <!-- Key Metrics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Total Revenue -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">{{ __('Total Revenue') }}</p>
                    <p class="text-3xl font-bold">€{{ number_format($analytics['revenue']['total'] ?? 0, 0) }}</p>
                    @if(isset($analytics['revenue']['growth']))
                        <p class="text-blue-100 text-sm">
                            <span class="{{ $analytics['revenue']['growth'] >= 0 ? 'text-green-200' : 'text-red-200' }}">
                                {{ $analytics['revenue']['growth'] >= 0 ? '+' : '' }}{{ number_format($analytics['revenue']['growth'], 1) }}%
                            </span>
                            {{ __('vs last period') }}
                        </p>
                    @endif
                </div>
                <div class="bg-blue-400 bg-opacity-50 rounded-full p-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Licenses -->
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">{{ __('Total Licenses') }}</p>
                    <p class="text-3xl font-bold">{{ number_format($analytics['licenses']['total'] ?? 0) }}</p>
                    @if(isset($analytics['licenses']['new_this_month']))
                        <p class="text-green-100 text-sm">
                            <span class="text-green-200">+{{ number_format($analytics['licenses']['new_this_month']) }}</span>
                            {{ __('this month') }}
                        </p>
                    @endif
                </div>
                <div class="bg-green-400 bg-opacity-50 rounded-full p-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Active Licenses -->
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium">{{ __('Active Licenses') }}</p>
                    <p class="text-3xl font-bold">{{ number_format($analytics['licenses']['active'] ?? 0) }}</p>
                    @if(isset($analytics['licenses']['total']) && $analytics['licenses']['total'] > 0)
                        <p class="text-purple-100 text-sm">
                            {{ number_format(($analytics['licenses']['active'] / $analytics['licenses']['total']) * 100, 1) }}%
                            {{ __('of total') }}
                        </p>
                    @endif
                </div>
                <div class="bg-purple-400 bg-opacity-50 rounded-full p-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l3 3 8-8"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Expiring Soon -->
        <div class="bg-gradient-to-r from-amber-500 to-amber-600 rounded-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-amber-100 text-sm font-medium">{{ __('Expiring Soon') }}</p>
                    <p class="text-3xl font-bold">{{ number_format($analytics['licenses']['expiring_soon'] ?? 0) }}</p>
                    <p class="text-amber-100 text-sm">{{ __('next 30 days') }}</p>
                </div>
                <div class="bg-amber-400 bg-opacity-50 rounded-full p-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.734-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Revenue Trend Chart -->
        <div class="bg-white rounded-lg border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-slate-800">{{ __('Revenue Trend') }}</h3>
                <select wire:model="revenuePeriod" class="form-select text-sm">
                    <option value="7">{{ __('Last 7 days') }}</option>
                    <option value="30">{{ __('Last 30 days') }}</option>
                    <option value="90">{{ __('Last 3 months') }}</option>
                    <option value="365">{{ __('Last year') }}</option>
                </select>
            </div>
            
            <div class="h-64">
                @if(isset($analytics['revenue_chart']) && count($analytics['revenue_chart']) > 0)
                    <canvas id="revenueChart" width="400" height="200"></canvas>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const ctx = document.getElementById('revenueChart').getContext('2d');
                            new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: @json(array_keys($analytics['revenue_chart'])),
                                    datasets: [{
                                        label: '{{ __("Revenue") }}',
                                        data: @json(array_values($analytics['revenue_chart'])),
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
                                            beginAtZero: true,
                                            ticks: {
                                                callback: function(value) {
                                                    return '€' + value.toLocaleString();
                                                }
                                            }
                                        }
                                    },
                                    plugins: {
                                        legend: {
                                            display: false
                                        }
                                    }
                                }
                            });
                        });
                    </script>
                @else
                    <div class="flex items-center justify-center h-full text-slate-500">
                        <div class="text-center">
                            <svg class="w-12 h-12 mx-auto text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <p>{{ __('No revenue data available') }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- License Status Distribution -->
        <div class="bg-white rounded-lg border border-slate-200 p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">{{ __('License Status Distribution') }}</h3>
            
            <div class="h-64">
                @if(isset($analytics['status_distribution']) && count($analytics['status_distribution']) > 0)
                    <canvas id="statusChart" width="400" height="200"></canvas>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const ctx = document.getElementById('statusChart').getContext('2d');
                            new Chart(ctx, {
                                type: 'doughnut',
                                data: {
                                    labels: @json(array_keys($analytics['status_distribution'])),
                                    datasets: [{
                                        data: @json(array_values($analytics['status_distribution'])),
                                        backgroundColor: [
                                            '#10b981', // Active - green
                                            '#f59e0b', // Pending - amber
                                            '#ef4444', // Suspended - red
                                            '#6b7280'  // Expired - gray
                                        ],
                                        borderWidth: 2,
                                        borderColor: '#ffffff'
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            position: 'bottom'
                                        }
                                    }
                                }
                            });
                        });
                    </script>
                @else
                    <div class="flex items-center justify-center h-full text-slate-500">
                        <div class="text-center">
                            <svg class="w-12 h-12 mx-auto text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                            </svg>
                            <p>{{ __('No status data available') }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Detailed Statistics -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Top Performing Licenses -->
        <div class="bg-white rounded-lg border border-slate-200 p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">{{ __('Top Performing Licenses') }}</h3>
            
            @if(isset($analytics['top_licenses']) && count($analytics['top_licenses']) > 0)
                <div class="space-y-3">
                    @foreach($analytics['top_licenses'] as $license)
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                            <div class="flex-1">
                                <div class="font-medium text-slate-900">{{ $license['name'] }}</div>
                                <div class="text-sm text-slate-600">{{ $license['code'] }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-semibold text-slate-900">{{ number_format($license['count']) }}</div>
                                <div class="text-xs text-slate-500">{{ __('licenses') }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-slate-500">
                    <p>{{ __('No license data available') }}</p>
                </div>
            @endif
        </div>

        <!-- Geographic Distribution -->
        <div class="bg-white rounded-lg border border-slate-200 p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">{{ __('Geographic Distribution') }}</h3>
            
            @if(isset($analytics['geographic_distribution']) && count($analytics['geographic_distribution']) > 0)
                <div class="space-y-3">
                    @foreach($analytics['geographic_distribution'] as $country)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center flex-1">
                                <div class="font-medium text-slate-900">{{ $country['name'] }}</div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="bg-blue-100 rounded-full px-2 py-1">
                                    <span class="text-xs font-medium text-blue-800">{{ number_format($country['count']) }}</span>
                                </div>
                                <div class="w-16 bg-slate-200 rounded-full h-2">
                                    <div class="bg-blue-500 h-2 rounded-full" style="width: {{ ($country['count'] / max(array_column($analytics['geographic_distribution'], 'count'))) * 100 }}%"></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-slate-500">
                    <p>{{ __('No geographic data available') }}</p>
                </div>
            @endif
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-lg border border-slate-200 p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">{{ __('Recent Activity') }}</h3>
            
            @if(isset($analytics['recent_activity']) && count($analytics['recent_activity']) > 0)
                <div class="space-y-3">
                    @foreach($analytics['recent_activity'] as $activity)
                        <div class="flex items-start space-x-3 p-3 bg-slate-50 rounded-lg">
                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center justify-center h-8 w-8 rounded-full 
                                    {{ $activity['type'] === 'created' ? 'bg-green-100 text-green-600' : '' }}
                                    {{ $activity['type'] === 'suspended' ? 'bg-yellow-100 text-yellow-600' : '' }}
                                    {{ $activity['type'] === 'activated' ? 'bg-blue-100 text-blue-600' : '' }}">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-900">{{ $activity['description'] }}</p>
                                <p class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($activity['created_at'])->diffForHumans() }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-slate-500">
                    <p>{{ __('No recent activity') }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Export and Actions -->
    <div class="bg-white rounded-lg border border-slate-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-slate-800">{{ __('Export and Reports') }}</h3>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <button wire:click="exportAnalytics('pdf')" class="btn btn-secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                {{ __('Export PDF Report') }}
            </button>
            
            <button wire:click="exportAnalytics('excel')" class="btn btn-secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                {{ __('Export Excel') }}
            </button>
            
            <button wire:click="sendAnalyticsEmail" class="btn btn-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                {{ __('Email Report') }}
            </button>
            
            <button wire:click="refreshAnalytics" class="btn btn-secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                {{ __('Refresh Data') }}
            </button>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>