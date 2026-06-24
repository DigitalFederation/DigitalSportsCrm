<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">
                    @php
                        $committeeTranslationKey = 'attachments.committees.' . $committee->code;
                        $committeeName = __($committeeTranslationKey) !== $committeeTranslationKey
                            ? __($committeeTranslationKey)
                            : ucwords(strtolower($committee->code));
                    @endphp
                    {{ __('attachments.committee_files_title', ['committee' => $committeeName]) }}
                </h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

            </div>
        </div>

        <!-- FILTER RESULTS -->
        <x-filter-form :post="route(empty($committee)?'individual.attachments.index':'individual.committee.attachments.index', $committee->id ?? null)">
            <x-forms.filter-input-text :label="__('attachments.filters.file_name')" name="filter_name"/>
            <x-forms.filter-input-select :label="__('attachments.filters.category')" name="filter_category" :options="$categories"/>
            <x-forms.filter-input-select :label="__('attachments.filters.language')" name="filter_language" :options="$languages"/>
        </x-filter-form>

        @if (!empty($attachments) && $attachments->count() > 0)
            <x-attachments-index-table :attachments="$attachments" />

            <!-- Pagination -->
            <div class="mt-4">
                {{ $attachments->links() }}
            </div>
        @else
            <!-- No documents uploaded -->
            <div class="sm:flex sm:space-x-4 text-center ">
                <p class="mt-2 md:mt-4 text-center text-gray-700 text-xl font-bold mx-auto">
                    {{ __('attachments.no_files_uploaded') }} </p>
            </div>

        @endif


</x-layout>
