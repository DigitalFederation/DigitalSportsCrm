@props(['attachments', 'committee' => null])

<div class="sm:flex sm:justify-center sm:items-center mb-5">
    <div class="bg-white shadow-lg rounded-sm border border-slate-200 mb-8 w-full">
        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="table-auto w-full">
                <!-- Table header -->
                <thead
                    class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                <tr>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">{{ __('attachments.table.document') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">{{ __('attachments.table.category') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">{{ __('attachments.table.language') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-right"></div>
                    </th>
                </tr>
                </thead>
                <!-- Table body -->
                <tbody class="text-sm divide-y divide-slate-200">
                @foreach ($attachments as $attachment)

                    @if ($attachment->media->isNotEmpty())
                        <tr class="hover:bg-slate-50 bg-white">
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-left">
                                <div class="flex items-center gap-2">
                                    @foreach($attachment->media as $media)
                                        @if($media->mime_type == 'image/jpeg' || $media->mime_type == 'image/png')
                                            @include('components.svg.filetype-jpg')
                                        @elseif($media->mime_type == 'application/pdf')
                                            @include('components.svg.filetype-pdf')
                                        @elseif($media->mime_type == 'application/msword')
                                            @include('components.svg.filetype-doc')
                                        @elseif(
                                            $media->mime_type == 'application/vnd.ms-powerpoint' ||
                                            $media->mime_type == 'application/vnd.openxmlformats-officedocument.presentationml.presentation')
                                            @include('components.svg.filetype-ppt')
                                        @elseif($media->mime_type == 'video/mp4')
                                            @include('components.svg.filetype-doc')
                                        @elseif($media->mime_type == 'audio/mpeg')
                                            @include('components.svg.filetype-all')
                                        @else
                                            @include('components.svg.filetype-all')
                                        @endif
                                    @endforeach
                                    <div>
                                        <span>{{ $attachment->name }}</span>
                                        <div class="text-xs text-slate-600">
                                            {{ $media->mime_type }} | {{ formatBytes($media->size) }}
                                        </div>
                                    </div>
                                    @if($attachment->media->count() > 1)
                                        <span class="ml-2 bg-slate-200 text-slate-600 px-2 py-1 rounded-full text-xs">
                                            {{ $attachment->media->count() }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-left">
                                {{ __('attachments.categories.' . $attachment->category->name, [], app()->getLocale()) !== 'attachments.categories.' . $attachment->category->name ? __('attachments.categories.' . $attachment->category->name) : $attachment->category->name }}
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-left">
                                {{ $attachment->language?->name }}
                            </td>

                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-right">
                                <div class="flex items-center justify-end text-right gap-x-2">
                                    @foreach($attachment->media as $media)
                                        <form action="{{ route(request()->segment(1).'.attachments.download', $media->id) }}"
                                              method="POST">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $media->id }}">
                                            <button type="submit" class="text-slate-500 hover:text-slate-800">
                                                @include('components.svg.box-arrow-down', ['class' => 'h-5 w-5'])
                                            </button>
                                        </form>
                                    @endforeach

                                    @if($attachment->owner_type == 'federation' &&
                                        $attachment->owner_id == auth()->user()->federations?->first()?->id)
                                        <form
                                            action="{{ route('federation.attachments.destroy', $attachment->id) }}"
                                            method="POST"
                                            onsubmit="return confirm('{{ __('attachments.confirm_delete') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800">
                                                @include('components.svg.trash', ['class' => 'h-5 w-5'])
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endif
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
