<x-layout>
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Profile Hero Card -->
            <x-individual.profile-hero :individual="$individual" individualType="individual" />

            <!-- Profile Tabs -->
            <x-individual.profile-tabbed
                :individual="$individual"
                :context="'individual'"
                :showUserAccount="false"
                :showProfessionalRoles="false"
                :showDocuments="false"
            />

            <!-- Recent Actions -->
            <div class="mt-6 bg-white shadow-sm border border-gray-200 rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('main.recent_actions') }}</h2>
                </div>
                <div class="p-6">
                    <livewire:widgets.activity-log />
                </div>
            </div>
        </div>
    </div>
</x-layout>
