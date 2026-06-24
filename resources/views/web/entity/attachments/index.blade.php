<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">
                    @if($committee && $committee->code)
                        {{ __('attachments.entity_committee_titles.' . $committee->code) }}
                    @else
                        {{ __('attachments.federation_files') }}
                    @endif
                </h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2"></div>
        </div>

        <!-- FILTER RESULTS -->
        <x-filter-form :post="route(empty($committee)?'entity.attachments.index':'entity.committee.attachments.index', $committee->id ?? null)">
            <x-forms.filter-input-text :label="__('attachments.filters.file_name')" name="filter_name"/>
            <x-forms.filter-input-select label="Category" name="filter_category" :options="$categories"/>
            <x-forms.filter-input-select label="Language" name="filter_language" :options="$languages"/>
            <x-forms.filter-input-date-range label="Date" nameStart="filter_date_start" nameEnd="filter_date_end"/>
        </x-filter-form>

        @if (!empty($attachments) && $attachments->count() > 0)
            <x-attachments.attachments-index-table :attachments="$attachments" :committee="$committee" />
        @else
            <!-- No documents uploaded -->
            <div class="sm:flex sm:space-x-4 text-center ">
                <p class="mt-2 md:mt-4 text-center text-gray-700 text-xl font-bold mx-auto">
                    {{ __('No files available') }}
                </p>
            </div>

    @endif
</x-layout>
