<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5 mt-5">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('import.entity_import_title') }}</h1>
                <p class="text-sm text-slate-600 mt-1">{{ __('import.entity_choose_file_description') }}</p>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

                <a class="btn btn-secondary" href="{{ route('admin.entity.import.template') }}">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M11 0H3c-.6 0-1 .4-1 1v14c0 .6.4 1 1 1h10c.6 0 1-.4 1-1V3l-3-3zM3 14V2h7v2h2v10H3z" />
                        <path d="M8 6.5L5.5 9H7v4h2V9h1.5L8 6.5z" />
                    </svg>
                    <span class="hidden xs:block ml-2">{{ __('main.download_template') }}</span>
                </a>

                <a class="btn btn-secondary" href="{{ route('admin.entity.index') }}">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M10.5 8L6 3.5 7.5 2 14 8.5l-6.5 6.5L6 13.5 10.5 9H2V7h8.5z" />
                    </svg>
                    <span class="hidden xs:block ml-2">{{ __('main.back_to_entities') }}</span>
                </a>

            </div>

        </div>

        <!-- Help Section -->
        <div class="information-box mb-8">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">{{ __('main.import_guidelines') }}</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="space-y-1">
                            <li>{{ __('main.supported_formats') }}</li>
                            <li>{{ __('main.entity_required_fields_info') }}</li>
                            <li>{{ __('main.duplicate_skip_info') }}</li>
                            <li>{{ __('main.bulk_processing_info') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Import Wizard -->
        <div class="card mb-8">
            @livewire('admin.entity-import-wizard')
        </div>

    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        window.addEventListener('import-completed', function() {
            setTimeout(() => {
                window.location.href = "{{ route('admin.entity.index') }}";
            }, 3000);
        });
    });
    </script>
    @endpush
</x-layout>
