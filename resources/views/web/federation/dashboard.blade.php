@section('title', __('dashboard.federation_dashboard'))
<x-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-6 sm:py-8 w-full max-w-9xl mx-auto">

        <!-- Welcome Banner with Federation Header -->
        <livewire:federation.dashboard.federation-welcome-header />

        <!-- ============================================ -->
        <!-- AREA 1: Actions & Approvals (No Title) -->
        <!-- ============================================ -->

        <!-- Recent Activity -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mb-5 sm:mb-6 overflow-hidden">
            <div class="flex items-center justify-between px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-white dark:from-gray-800 dark:to-gray-800">
                <div class="flex items-center gap-2 sm:gap-3">
                    <div class="flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-indigo-100 dark:bg-indigo-900/30">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-indigo-600 dark:text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">
                        {{ __('dashboard.recent_actions') }}
                    </h2>
                </div>
            </div>
            <div class="p-4 sm:p-6">
                <livewire:widgets.activity-log />
            </div>
        </div>

        <!-- Pending Approvals Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-8 sm:mb-10">
            <livewire:federation.dashboard.pending-entity-approvals-widget />
            <livewire:federation.dashboard.pending-individual-approvals-widget />
        </div>

        <!-- ============================================ -->
        <!-- AREA 2: Statistics Section (With Title) -->
        <!-- ============================================ -->

        <!-- Statistics Section Header -->
        <div class="mb-5 sm:mb-6">
            <div class="flex items-center gap-2 sm:gap-3">
                <div class="flex items-center justify-center w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/30">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                    </svg>
                </div>
                <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white">
                    {{ __('dashboard.statistics_section') }}
                </h2>
            </div>
            <div class="mt-2 h-1 w-16 sm:w-20 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full"></div>
        </div>

        <!-- 1. Members Distribution Table (Full Width) -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mb-5 sm:mb-6 overflow-hidden">
            <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-white dark:from-gray-800 dark:to-gray-800">
                <div class="flex items-center gap-2 sm:gap-3">
                    <div class="flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-violet-100 dark:bg-violet-900/30">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-violet-600 dark:text-violet-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-semibold text-gray-900 dark:text-white">
                            {{ __('dashboard.members_distribution_title') }}
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            {{ __('dashboard.members_distribution_desc') }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="p-3 sm:p-4">
                <livewire:federation.dashboard.members-distribution-table />
            </div>
        </div>

        <!-- 2. Charts Row - Entities by District + Individuals by District -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-5 sm:mb-6">
            @if (!$isModalidadeAssociation)
            <!-- Entities by District Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-white dark:from-gray-800 dark:to-gray-800">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <div class="flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-blue-100 dark:bg-blue-900/30">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-semibold text-gray-900 dark:text-white">
                                {{ __('dashboard.entities_by_district') }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                {{ __('dashboard.total_active_entities') }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="p-4 sm:p-6">
                    <livewire:federation.dashboard.entities-by-district-chart />
                </div>
            </div>
            @endif

            <!-- Individuals by District Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden {{ $isModalidadeAssociation ? 'lg:col-span-2' : '' }}">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-white dark:from-gray-800 dark:to-gray-800">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <div class="flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-emerald-100 dark:bg-emerald-900/30">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-semibold text-gray-900 dark:text-white">
                                {{ __('dashboard.individuals_by_district') }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                {{ __('dashboard.total_active_individual_members') }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="p-4 sm:p-6">
                    <livewire:federation.dashboard.individuals-by-district-chart />
                </div>
            </div>
        </div>

        @if (!$isModalidadeAssociation)
        <!-- 3. Charts Row - Affiliation Revenue -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-5 sm:mb-6">
            <!-- Entity Affiliation Revenue -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-white dark:from-gray-800 dark:to-gray-800">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <div class="flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-amber-100 dark:bg-amber-900/30">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-amber-600 dark:text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-semibold text-gray-900 dark:text-white">
                                {{ __('dashboard.annual_entity_affiliation_revenue') }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                {{ __('dashboard.entity_affiliation_revenue_desc') }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="p-4 sm:p-6">
                    <livewire:federation.dashboard.annual-entity-affiliation-revenue-chart />
                </div>
            </div>

            <!-- Individual Affiliation Revenue -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-white dark:from-gray-800 dark:to-gray-800">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <div class="flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-rose-100 dark:bg-rose-900/30">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-rose-600 dark:text-rose-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-semibold text-gray-900 dark:text-white">
                                {{ __('dashboard.annual_individual_affiliation_revenue') }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                {{ __('dashboard.individual_affiliation_revenue_desc') }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="p-4 sm:p-6">
                    <livewire:federation.dashboard.annual-individual-affiliation-revenue-chart />
                </div>
            </div>
        </div>
        @endif

        @if ($showLicenseRevenueCharts)
        <!-- 4. Charts Row - License Revenue -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-5 sm:mb-6">
            <!-- Entity License Revenue -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-white dark:from-gray-800 dark:to-gray-800">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <div class="flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-violet-100 dark:bg-violet-900/30">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-violet-600 dark:text-violet-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-semibold text-gray-900 dark:text-white">
                                {{ __('dashboard.annual_entity_license_revenue') }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                {{ __('dashboard.license_revenue_organization_only') }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="p-4 sm:p-6">
                    <livewire:federation.dashboard.annual-entity-license-revenue-chart />
                </div>
            </div>

            <!-- Individual License Revenue -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-white dark:from-gray-800 dark:to-gray-800">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <div class="flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-pink-100 dark:bg-pink-900/30">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-pink-600 dark:text-pink-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-semibold text-gray-900 dark:text-white">
                                {{ __('dashboard.annual_individual_license_revenue') }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                {{ __('dashboard.license_revenue_organization_only') }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="p-4 sm:p-6">
                    <livewire:federation.dashboard.annual-individual-license-revenue-chart />
                </div>
            </div>
        </div>

        <!-- 5. Charts Row - Sport Licenses by Role -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-5 sm:mb-6">
            <!-- Entity Sport Licenses -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-white dark:from-gray-800 dark:to-gray-800">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <div class="flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-purple-100 dark:bg-purple-900/30">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-purple-600 dark:text-purple-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-semibold text-gray-900 dark:text-white">
                                {{ __('dashboard.entity_sport_licenses') }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                {{ __('dashboard.entity_sport_licenses_desc') }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="p-4 sm:p-6">
                    <livewire:federation.dashboard.entity-sport-licenses-chart />
                </div>
            </div>

            <!-- Individual Sport Licenses by Role -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-white dark:from-gray-800 dark:to-gray-800">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <div class="flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-blue-100 dark:bg-blue-900/30">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-semibold text-gray-900 dark:text-white">
                                {{ __('dashboard.individual_sport_licenses_by_role') }}
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                {{ __('dashboard.individual_sport_licenses_desc') }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="p-4 sm:p-6">
                    <livewire:federation.dashboard.individual-sport-licenses-by-role-chart />
                </div>
            </div>
        </div>
        @endif

        @if (!$isModalidadeAssociation)
        <!-- 6. Entity Billing Table (Ranking) -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-white dark:from-gray-800 dark:to-gray-800">
                <div class="flex items-center gap-2 sm:gap-3">
                    <div class="flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-lg bg-emerald-100 dark:bg-emerald-900/30">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-emerald-600 dark:text-emerald-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 002.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 012.916.52 6.003 6.003 0 01-5.395 4.972m0 0a6.726 6.726 0 01-2.749 1.35m0 0a6.772 6.772 0 01-3.044 0" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-semibold text-gray-900 dark:text-white">
                            {{ __('dashboard.entity_billing_title') }}
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            {{ __('dashboard.entity_billing_affiliation_desc') }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="p-3 sm:p-4">
                <livewire:federation.dashboard.entity-billing-table />
            </div>
        </div>
        @endif
    </div>
</x-layout>
