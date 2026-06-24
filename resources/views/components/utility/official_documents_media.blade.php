@if(!empty($attachments) && $attachments->count() > 0)

    <section class="mt-2 bg-white rounded-md shadow hover:shadow-xl">
        <div class="mx-5 mt-4 mb-2">
            <h2 class="font-semibold text-slate-600 text-lg">{{ __('Official Documents')}}</h2>
        </div>

        <x-dynamic-table
            :headers="['File', 'File Name', 'FileType', 'Date', ['text'=>'Status','alignment'=>'text-right'] ,'']">
            @foreach($attachments as $document)
                @php
                    $mediaItem = $document->getMedia('media')->first();
                    $fileType = $mediaItem?->mime_type ?? 'N/A';
                    $fileSize = $mediaItem?->human_readable_size ?? 'N/A';
                @endphp
                <tr>
                    <td class="pl-5  py-3 break-words">
                        {{ \App\Enums\OfficialDocumentTypeEnum::toString($document->type) }}
                    </td>
                    <td class="px-2  last:pr-5 py-3 whitespace-nowrap text-left">
                        {{ $document->getMedia('media')->value('name') }}
                    </td>
                    <td class="px-2  last:pr-5 py-3 whitespace-nowrap text-left">
                        <div>{{ $fileType }}</div> <div class="text-xs">{{ $fileSize }}</div>
                    </td>
                    <td class="px-2  last:pr-5 py-3 whitespace-nowrap text-left">
                        <div>{{ \Carbon\Carbon::parse($document->created_at)->format('d/m/Y') }}</div>
                        <small>({{ \Carbon\Carbon::parse($document->created_at)->diffForHumans() }})</small>
                    </td>
                    <td class="px-2 last:pr-5 py-3 whitespace-nowrap text-right">
                        <x-tables.badge :status="ucfirst($document->stateName())" :color="$document->stateColor()"/>
                    </td>
                    <td class="px-2 py-3 text-right">
                        <x-dynamic-table-buttons
                            type="download"
                            class="w-6 h-6"
                            method="POST"
                            route="{{ route(Request::segment(1).'.official-documents.download', $document->id) }}"
                            title="{{ __('Download') }}"
                            class="text-green-500 hover:text-green-600 rounded-full cursor-pointer"></x-dynamic-table-buttons>
                    </td>
                </tr>
            @endforeach
        </x-dynamic-table>
    </section>
@endif
