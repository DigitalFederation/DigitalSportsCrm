<x-public-layout>
    <div class="min-h-screen flex items-center justify-center px-4 md:px-6 bg-cover bg-waves animate-in">
        <div class="max-w-xl w-full">
            <div class="flex flex-col items-center mb-6">
                <img src="{{ asset(config('branding.primary.logo_path', 'img/project-logo.svg')) }}" alt="{{ config('branding.primary.short_name', 'DF') }}" class="h-28 md:h-32 lg:h-40">
                <div class="mt-3">
                    <x-ui.badge variant="blue" size="sm">419</x-ui.badge>
                </div>
            </div>
            <x-ui.empty-state-card
                :title="__('errors.page_expired.title')"
                :description="__('errors.page_expired.description')"
                class="shadow-lg"
            >
                <div class="flex items-center justify-center gap-2">
                    <x-ui.button variant="secondary" type="button" onclick="window.location.reload()">
                        {{ __('errors.actions.try_again') }}
                    </x-ui.button>
                    <x-ui.button variant="primary" href="{{ url('/') }}">
                        {{ __('errors.actions.go_home') }}
                    </x-ui.button>
                </div>
            </x-ui.empty-state-card>
        </div>
    </div>
</x-public-layout>
