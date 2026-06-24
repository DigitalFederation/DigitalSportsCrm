<x-layout>
    @php
        $isFederationContext = isset($context) && $context === 'federation';
        $isAdminContext = isset($context) && $context === 'admin';
        $namespace = Request::segment(1);
    @endphp

    <div class="previous-layout-classes">
        <!-- Back Button and Actions -->
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
            <a href="{{ URL::previous() }}"
               class="btn btn-sm btn-secondary inline-flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
                <span class="ml-2">{{ __('Back') }}</span>
            </a>

            <div class="flex gap-2">
                <a class="btn btn-primary" href="{{ route($namespace . '.entity.edit', $entity->id) }}">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    {{ __('Edit') }}
                </a>

                @if ($isAdminContext && $entity->users->first()?->id && auth()->user()->can('impersonate users'))
                    <a href="{{ route('admin.impersonate.start', $entity->users->first()->id) }}"
                       class="btn btn-sm text-white px-2 py-1 bg-amber-500 hover:bg-amber-600 rounded"
                       title="{{ __('main.impersonate') }}"
                       onclick="return confirm('{{ __('main.impersonate_confirm') }}')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </a>
                @endif
            </div>
        </div>

        <!-- Hero Section with Profile Info -->
        <x-entity.profile-hero :entity="$entity" />

        <!-- Tabs Content -->
        <x-entity.profile-tabbed
            :entity="$entity"
            :context="$context ?? null"
            :showUserAccount="$isAdminContext"
            :showDocuments="true"
            :documents="$documents ?? collect()"
        />
    </div>
</x-layout>
