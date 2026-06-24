@props([
    'report',
    'event',
    'uploadRoute',
    'downloadRoutePrefix',
    'deleteRoutePrefix',
])

<div class="card">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('events.attached_documents') }}</h2>

    @if($report?->documents?->count() > 0)
        <div class="mb-6 space-y-2">
            @foreach($report->documents as $document)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition-colors">
                    <div class="flex items-center min-w-0">
                        <div class="flex-shrink-0 w-10 h-10 bg-slate-200 rounded-lg flex items-center justify-center">
                            @php
                                $extension = pathinfo($document->file_name, PATHINFO_EXTENSION);
                                $icon = match(strtolower($extension)) {
                                    'pdf' => 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z',
                                    'doc', 'docx' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                                    'jpg', 'jpeg', 'png' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
                                    default => 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z'
                                };
                            @endphp
                            <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $icon }}"/>
                            </svg>
                        </div>
                        <div class="ml-3 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $document->file_name }}</p>
                            <p class="text-xs text-gray-500">{{ $document->formatted_file_size }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 ml-4">
                        <a href="{{ route($downloadRoutePrefix, [$event, $document]) }}"
                           class="text-indigo-600 hover:text-indigo-800 text-sm font-medium transition-colors">
                            {{ __('events.download') }}
                        </a>
                        @if(! $report->is_submitted)
                            <form action="{{ route($deleteRoutePrefix, [$event, $document]) }}"
                                  method="POST"
                                  class="inline"
                                  onsubmit="return confirm('{{ __('events.confirm_delete_document') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium transition-colors">
                                    {{ __('events.delete') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="mb-6 py-8 text-center bg-gray-50 rounded-lg border-2 border-dashed border-gray-200">
            <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="mt-2 text-sm text-gray-500">{{ __('events.no_documents_attached') }}</p>
        </div>
    @endif

    @if($report && ! $report->is_submitted)
        <form action="{{ route($uploadRoute, $event) }}"
              method="POST"
              enctype="multipart/form-data"
              class="flex items-end gap-4 p-4 bg-slate-50 rounded-lg border border-slate-200">
            @csrf
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('events.upload_document') }}</label>
                <input type="file"
                       name="document"
                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                       class="block w-full text-sm text-gray-600
                              file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0
                              file:text-sm file:font-medium
                              file:bg-indigo-600 file:text-white
                              hover:file:bg-indigo-700 file:cursor-pointer file:transition-colors">
                <p class="text-xs text-gray-500 mt-1.5">{{ __('events.allowed_file_types') }}</p>
            </div>
            <button type="submit" class="btn btn-secondary whitespace-nowrap">
                {{ __('events.upload') }}
            </button>
        </form>
    @elseif(! $report)
        <p class="text-sm text-gray-500 italic">{{ __('events.save_report_first_to_upload') }}</p>
    @endif
</div>
