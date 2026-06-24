@if(!empty($attachments))
    <div class="card mt-6">
        <header class="mb-4">
            <h2 class="font-semibold text-slate-800">
                {{ !empty($sectionTitle) ? $sectionTitle : __('Media Documents') }}
            </h2>
        </header>
        <div class="overflow-x-auto">
            <table class="table-auto w-full">
                <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                <tr>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">{{ __('File') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-center">{{ __('Date') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3">
                    </th>
                </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-200">
                @foreach($attachments as $document)
                    <tr>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 break-words">
                            <a href="{{ route('admin.media.download', $document->id) }}">
                              {{ $document->file_name }}
                            </a>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-center">
                            <div>{{ \Carbon\Carbon::parse($document->created_at)->format('d-m-Y') }}</div>
                            <small>({{ \Carbon\Carbon::parse($document->created_at)->diffForHumans() }})</small>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 text-right">

                            <form action="{{ route('admin.media.download') }}" method="POST" enctype="multipart/form-data" class="w-auto">
                            @csrf
                                <input type="hidden" name="id" value="{{ $document->id }}">
                                <button type="submit" class="btn-sm border-slate-200 hover:border-slate-300 shadow-sm">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 00-2.25 2.25v9a2.25 2.25 0 002.25 2.25h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25H15M9 12l3 3m0 0l3-3m-3 3V2.25" />
                                        </svg>
                                        {{ __('Download') }}
                                    </div>
                                </button>
                            </form>

                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif
