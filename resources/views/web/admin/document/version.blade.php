<x-layout>
    <div class="previous-layout-classes">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('Document Detail - Version') }} {{ $document->version_id }}</h1>
            </div>

            <!-- Right: Actions -->
            <div class="flex flex-col md:flex-row justify-start sm:justify-end gap-2">

                <a class="btn btn-info" href="{{ route('admin.document.index') }}">
                    {{ __('Back') }}
                </a>

                @if($document->stateName() === 'pending')
                    <!-- Cancel Document Form -->
                    <form action="{{ route('admin.document.cancel', $document) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger w-full md:w-auto"
                                onclick="return confirm('{{ __('Are you sure you want to cancel this document?') }}')">
                            {{ __('Cancel') }}
                        </button>
                    </form>
                @endif


                <a class="btn btn-primary w-full" href="{{ route('admin.document.notify', $document) }}">
                    {{ __('Resend notification') }}
                </a>


                @if($document->stateName() === 'canceled')
                    <!-- Cancel Document Form -->
                    <form action="{{ route('admin.document.delete-canceled', $document) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="btn btn-danger w-full md:w-auto"
                                onclick="return confirm('{{ __('Are you sure you want to delete this document? This is irreversible.') }}')">
                            {{ __('Delete Document') }}
                        </button>
                    </form>
                @endif

            </div>

        </div>

        <div class="flex flex-col md:flex-row gap-x-4 gap-y-4 items-start">

            <div
                class="@if($document->stateName() == 'pending' || $document->stateName() == 'paid') md:w-2/3 @else md:w-full @endif">
                <x-document.detail :document="$document"></x-document.detail>
            </div>

            <div class="w-full md:w-1/3">
                <livewire:widget-document-activity-log :document="$document" />

                @if($document->stateName() == 'pending' || $document->stateName() == 'paid')
                    <div class="w-full bg-white border-zinc-200 border h-full p-4 rounded-md">

                        @if($document->stateName() == 'pending')
                            <x-document.card_mark_as_paid :document="$document"
                                                          :relatedDocuments="$relatedDocuments"></x-document.card_mark_as_paid>
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
