<div class="space-y-6">
    <!-- Presidente da Direção -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ __('federation.board_president') }}
        </label>

        @if($boardPresident)
            <div class="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0">
                        <img class="h-10 w-10 rounded-full object-cover"
                             src="{{ $boardPresident->getFirstMediaUrl('profile', 'thumb') ?: asset('img/user_placeholder.png') }}"
                             alt="{{ $boardPresident->native_name }}">
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $boardPresident->native_name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('federation.member_number') }}: {{ $boardPresident->member_number }}</p>
                    </div>
                </div>
                <button type="button"
                        wire:click="removeBoardPresident"
                        class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @else
            <div class="flex gap-2">
                <input type="text"
                       wire:model="boardPresidentSearch"
                       wire:keydown.enter="searchBoardPresident"
                       placeholder="{{ __('federation.enter_individual_id_placeholder') }}"
                       class="form-input flex-1 {{ $boardPresidentError ? 'border-red-300' : '' }}">
                <button type="button"
                        wire:click="searchBoardPresident"
                        class="btn bg-primary-500 hover:bg-primary-600 text-white">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
            </div>
            @if($boardPresidentError)
                <p class="mt-1 text-xs text-red-500">{{ $boardPresidentError }}</p>
            @endif
        @endif

        @if($boardPresidentSuccess)
            <p class="mt-1 text-xs text-green-600 dark:text-green-400">{{ __('federation.member_associated_success') }}</p>
        @endif
    </div>

    <!-- Presidente da Mesa de Assembleia Geral -->
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ __('federation.assembly_president') }}
        </label>

        @if($assemblyPresident)
            <div class="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0">
                        <img class="h-10 w-10 rounded-full object-cover"
                             src="{{ $assemblyPresident->getFirstMediaUrl('profile', 'thumb') ?: asset('img/user_placeholder.png') }}"
                             alt="{{ $assemblyPresident->native_name }}">
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $assemblyPresident->native_name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('federation.member_number') }}: {{ $assemblyPresident->member_number }}</p>
                    </div>
                </div>
                <button type="button"
                        wire:click="removeAssemblyPresident"
                        class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @else
            <div class="flex gap-2">
                <input type="text"
                       wire:model="assemblyPresidentSearch"
                       wire:keydown.enter="searchAssemblyPresident"
                       placeholder="{{ __('federation.enter_individual_id_placeholder') }}"
                       class="form-input flex-1 {{ $assemblyPresidentError ? 'border-red-300' : '' }}">
                <button type="button"
                        wire:click="searchAssemblyPresident"
                        class="btn bg-primary-500 hover:bg-primary-600 text-white">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
            </div>
            @if($assemblyPresidentError)
                <p class="mt-1 text-xs text-red-500">{{ $assemblyPresidentError }}</p>
            @endif
        @endif

        @if($assemblyPresidentSuccess)
            <p class="mt-1 text-xs text-green-600 dark:text-green-400">{{ __('federation.member_associated_success') }}</p>
        @endif
    </div>

    <p class="text-xs text-gray-500 dark:text-gray-400">
        {{ __('federation.board_members_help') }}
    </p>
</div>
