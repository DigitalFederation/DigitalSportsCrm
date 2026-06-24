<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">
                    @if($committee)
                        {{ ucwords(strtolower($committee->code)) }}
                    @else
                        {{ config('branding.international.short_name', 'IF') }}
                    @endif
                    {{  __(' Files') }}
                </h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a href="@if($committee->exists) {{ route('federation.committee.attachments.index', $committee->id)  }} @else {{ route('federation.attachments.index')  }} @endif"
                   class="btn btn-info">
                    {{ __('Files to Download') }}
                </a>
                <a class="btn btn-primary"
                   href="@if($committee->exists) {{ route('federation.committee.attachments-sent.create', $committee->id) }} @else {{ route('federation.attachments-sent.create') }} @endif">
                    <span>{{ __('Upload new file') }}</span>
                </a>
            </div>
        </div>

        <!-- FILTER RESULTS -->
        <x-filter-form
            :post="route(empty($committee)?'federation.attachments.index':'federation.committee.attachments.index', $committee->id ?? null)">
            <x-forms.filter-input-text label="File name" name="filter_name" />
            <x-forms.filter-input-select label="Category" name="filter_category" :options="$categories" />
            <x-forms.filter-input-select label="Language" name="filter_language" :options="$languages" />
            <x-forms.filter-input-date-range label="Date" nameStart="filter_date_start" nameEnd="filter_date_end" />
        </x-filter-form>

        @if (!empty($attachments) && $attachments->count() > 0)
            <x-attachments.attachments-index-table :attachments="$attachments" />
        @else
            <!-- No documents uploaded -->
            <div class="sm:flex sm:space-x-4 text-center ">
                <p class="mt-2 md:mt-4 text-center text-gray-700 text-xl font-bold mx-auto">
                    {{ __('No files available') }} </p>
            </div>

    @endif
</x-layout>
