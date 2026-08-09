<x-public-layout>
    <div class="min-h-screen flex items-center justify-center px-4 md:px-6 bg-cover bg-waves animate-in">
        <div class="max-w-xl w-full">
            <div class="flex flex-col items-center mb-6">
                <x-brand-logo class="h-28 md:h-32 lg:h-40" text-class="text-3xl md:text-4xl font-bold text-slate-800" />
                <div class="mt-3">
                    <x-ui.badge variant="blue" size="sm">404</x-ui.badge>
                </div>
            </div>
            <x-ui.empty-state-card
                :title="__('errors.not_found.title')"
                :description="__('errors.not_found.description')"
                class="shadow-lg"
            >
                <div class="flex items-center justify-center gap-2">
                    <x-ui.button variant="secondary" :href="url()->previous()">
                        {{ __('errors.actions.go_back') }}
                    </x-ui.button>
                    <x-ui.button variant="primary" href="{{ url('/') }}">
                        {{ __('errors.actions.go_home') }}
                    </x-ui.button>
                </div>
            </x-ui.empty-state-card>
        </div>
    </div>
</x-public-layout>
