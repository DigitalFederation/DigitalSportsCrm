<x-layout>
    <div class="previous-layout-classes">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('documents.document_detail') }} {{ $document->number_extended }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="flex flex-col md:flex-row justify-start sm:justify-end gap-2">

                <a class="btn btn-info" href="{{ route('federation.document.index') }}">
                    {{ __('Back') }}
                </a>

            </div>

        </div>

        <div class="flex flex-col md:flex-row gap-x-4 gap-y-4 items-start">

            <div
                class="@if($document->stateName() == 'pending' || $document->stateName() == 'paid') md:w-2/3 @else w-full @endif">
                <x-document.detail :document="$document"></x-document.detail>
            </div>

            <div class="w-full md:w-1/3">
                <livewire:widget-document-activity-log :document="$document" />

                @if($document->stateName() == 'pending' || $document->stateName() == 'paid' || $document->stateName() == 'partially_paid')
                    <div class="w-full bg-white border-zinc-200 border h-full p-4 rounded-md">

                        @if(($document->stateName() == 'pending' || $document->stateName() == 'partially_paid') && ! ($isViewOnly ?? false))
                            <x-document.card_mark_as_paid
                                :document="$document"
                                :route="route('federation.document.manual-payment', $document)">
                            </x-document.card_mark_as_paid>
                        @elseif($document->stateName() == 'paid')
                            <x-document.card_is_paid :document="$document"
                                                     :relatedDocuments="$relatedDocuments"></x-document.card_is_paid>
                        @endif

                    </div>
                @endif
            </div>

        </div>
    </div>
</x-layout>
