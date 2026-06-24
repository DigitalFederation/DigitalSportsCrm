<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">
                    @if($committee)
                        {{ __('attachments.admin_committee_titles.' . $committee->code) }}
                    @else
                        {{ __('attachments.federation_files') }}
                    @endif
                </h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                @if($committee)
                    <a class="btn btn-primary" href="{{ route('admin.committee.attachments.create', $committee->id) }}">
                        @else
                            <a class="btn btn-primary" href="{{ route('admin.attachments.create') }}">
                                @endif
                                <span>{{ __('attachments.upload_new_file') }}</span>
                            </a>
            </div>
        </div>


        <!-- FILTER RESULTS -->
        <x-filter-form
            :post="route(empty($committee)?'admin.attachments.index':'admin.committee.attachments.index', $committee->id ?? null)">
            <x-forms.filter-input-text :label="__('attachments.filters.file_name')" name="filter_name" />
            <x-forms.filter-input-select :label="__('attachments.filters.category')" name="filter_category" :options="$categories" />
            <x-forms.filter-input-select :label="__('attachments.filters.language')" name="filter_language" :options="$languages" />
            <x-forms.filter-input-date-range :label="__('attachments.filters.date')" nameStart="filter_date_start" nameEnd="filter_date_end" />
        </x-filter-form>

        @if (!empty($attachments) && $attachments->count() > 0)
            <x-attachments-admin-index-table :attachments="$attachments" />

            <div class="mt-4">
                {{ $attachments->links() }}
            </div>
        @else
            <!-- No documents uploaded -->
            <div class="sm:flex flex-col text-center mx-auto">

                <p class="my-6 text-center text-gray-700 text-xl font-bold ">
                    {{ __('attachments.no_files_uploaded') }}
                </p>

                <a href="{{ route(Request::segment(1) . '.attachments.create', $committee->id ?? null) }}"
                   class="font-bold underline">{{ __('attachments.click_to_upload') }}</a>
            </div>

    @endif
</x-layout>
