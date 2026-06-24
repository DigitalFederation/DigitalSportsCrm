<x-layout>
    <div class="previous-layout-classes">
        
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('licenses.Manage Licenses for') }} {{ $federation->name }}</h1>
                <nav class="text-sm text-gray-600 mt-2">
                    <a href="{{ route('admin.federation.index') }}" class="hover:underline">{{ __('main.federations') }}</a>
                    <span class="mx-2">/</span>
                    <a href="{{ route('admin.federation.show', $federation) }}" class="hover:underline">{{ $federation->name }}</a>
                    <span class="mx-2">/</span>
                    <span>{{ __('licenses.Manage Licenses') }}</span>
                </nav>
            </div>
        </div>

        <livewire:admin.federation-license-manager :federation="$federation" />
        
    </div>
</x-layout>