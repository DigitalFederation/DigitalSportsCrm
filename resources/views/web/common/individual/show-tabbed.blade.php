<x-layout>
    {{-- Determine Federation context for specific elements --}}
    @php
        $isFederationContext = isset($context) && $context === 'federation';
        $isAdminContext = isset($context) && $context === 'admin';
        $isEntityContext = isset($context) && $context === 'entity';
        $loggedInFederation = $isFederationContext ? Auth::user()->federations()->first() : null;
    @endphp

    <div class="min-h-screen bg-gray-50">
        <!-- Modern Page Header -->
        <div class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="py-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">
                                {{ $individual->native_name }}
                            </h1>
                            @if ($individual->national_federation_number)
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ __('main.national_federation_number') }}:
                                    <span class="font-medium text-gray-700">{{ $individual->national_federation_number }}</span>
                                </p>
                            @endif
                        </div>
                        
                        <!-- Actions -->
                        <div class="flex items-center space-x-3">
                            <a href="{{ URL::previous() }}" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" class="w-4 h-4 mr-2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                </svg>
                                {{ __('Back') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <x-individual.profile-tabbed 
                :individual="$individual"
                :context="$context"
                :showUserAccount="$isAdminContext"
                :showProfessionalRoles="$isAdminContext || $isFederationContext"
                :showDocuments="($isAdminContext || $isFederationContext) && isset($official_documents)"
                :loggedInFederation="$loggedInFederation"
            />
        </div>
    </div>
</x-layout>