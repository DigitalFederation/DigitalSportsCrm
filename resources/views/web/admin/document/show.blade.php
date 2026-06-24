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

                <a class="btn btn-info" href="{{ route('admin.document.index') }}">
                    {{ __('common.back') }}
                </a>

                @if($document->stateName() === 'pending')
                    <!-- Cancel Document Form -->
                    <form action="{{ route('admin.document.cancel', $document) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger w-full md:w-auto"
                                onclick="return confirm('{{ __('documents.confirm_cancel_warning') }}')">
                            {{ __('common.cancel') }}
                        </button>
                    </form>
                @endif


                <a class="btn btn-primary w-full" href="{{ route('admin.document.notify', $document) }}">
                    {{ __('documents.resend_notification') }}
                </a>


                <!-- Delete Document Form (available for all states) -->
                <form action="{{ route('admin.document.delete', $document->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="btn btn-danger w-full md:w-auto"
                            onclick="return confirm('{{ __('documents.confirm_delete_warning') }}')">
                        {{ __('documents.delete_document') }}
                    </button>
                </form>

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

                        @if($document->stateName() == 'pending' || $document->stateName() == 'partially_paid')
                            <x-document.card_mark_as_paid :document="$document"></x-document.card_mark_as_paid>
                        @else
                            <x-document.card_is_paid :document="$document"
                                                     :relatedDocuments="$relatedDocuments"></x-document.card_is_paid>
                        @endif

                    </div>
                @endif
            </div>

        </div>
    </div>
</x-layout>
