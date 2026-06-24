<x-layout>
    {{-- Determine Federation context for specific elements --}}
    @php
        $isFederationContext = isset($context) && $context === 'federation';
        $isAdminContext = isset($context) && $context === 'admin';
        $isEntityContext = isset($context) && $context === 'entity';
        $loggedInFederation = $isFederationContext ? Auth::user()->federations()->first() : null;
    @endphp

    <div class="previous-layout-classes">
        <!-- Back Button & Action Buttons -->
        <div class="mb-4 flex justify-between items-center">
            <a href="{{ URL::previous() }}"
               class="btn btn-sm btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
                <span class="ml-2">{{ __('Back') }}</span>
            </a>

            @if ($isAdminContext)
                <div class="flex gap-2">
                    <a href="{{ route('admin.individual.edit', $individual) }}" class="btn btn-sm btn-primary">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        {{ __('main.edit') }}
                    </a>

                    @if ($individual->user && auth()->user()->can('impersonate users'))
                        <a href="{{ route('admin.impersonate.start', $individual->user->id) }}"
                           class="btn btn-sm text-white px-2 py-1 bg-amber-500 hover:bg-amber-600 rounded"
                           title="{{ __('main.impersonate') }}"
                           onclick="return confirm('{{ __('main.impersonate_confirm') }}')">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </a>
                    @endif
                </div>
            @endif
        </div>

        <!-- Hero Section with Profile Info -->
        <x-individual.profile-hero :individual="$individual" individualType="individual" :editable="$isEntityContext" />

        @if($isEntityContext)
            @livewire('entity.edit-member-photo', ['individual' => $individual])
        @endif

        <!-- Tabs Content -->
        <x-individual.profile-tabbed 
            :individual="$individual"
            :context="$context"
            :showUserAccount="$isAdminContext"
            :showProfessionalRoles="$isFederationContext"
            :showDocuments="$isAdminContext || $isFederationContext || $isEntityContext"
            :loggedInFederation="$loggedInFederation"
            :official_documents="$official_documents ?? null"
            :payment_documents="$payment_documents ?? null"
        />
    </div>
</x-layout>
