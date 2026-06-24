<div class="sm:flex sm:justify-center sm:items-center mb-5">
    <div class="bg-white shadow-lg rounded-lg border border-slate-200 mb-8 w-full">
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
                        <div class="font-semibold text-left">{{ __('attachments.table.date') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">{{ __('attachments.table.recipient') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">{{ __('attachments.table.organization') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-right"></div>
                    </th>
                </tr>
                </thead>
                <!-- Table body -->
                <tbody class="text-sm divide-y divide-slate-200">
                <!-- Row -->
                @foreach ($attachments as $attachment)
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
                            {{ $attachment->category->name }}

                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-left">
                            {{ $attachment->language?->name }}
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-left">
                            <div>{{ \Carbon\Carbon::parse($attachment->created_at)->format('d-m-Y') }}</div>
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3  w-px text-left">
                            {{ $attachment->recipient_description }}
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3  w-px text-left">
                            @if($attachment->owner_type == 'federation')
                                {{ $attachment->owner?->member_code }}
                            @else
                                {{ config('branding.primary.short_name', 'DF') }}
                            @endif
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px justify-end items-end text-right">
                            <div class="flex flex-row gap-x-2 items-center justify-end">
                                @foreach($attachment->media as $media)
                                    <form action="{{ route('admin.media.download') }}"
                                          method="POST">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $media->id }}">
                                        <button type="submit" class="text-slate-500 hover:text-slate-800">
                                            @include('components.svg.box-arrow-down', ['class' => 'h-5 w-5'])
                                        </button>
                                    </form>
                                @endforeach

                                <a href="{{ route('admin.attachments.edit', $attachment->id) }}"
                                   class="text-slate-500 hover:text-slate-800">
                                    @include('components.svg.edit', ['class' => 'h-5 w-5'])
                                </a>

                                <form
                                    action="{{ route('admin.attachments.destroy', $attachment->id) }}"
                                    method="POST"
                                    onsubmit="return confirm('{{ __('attachments.confirm_delete') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">
                                        @include('components.svg.trash', ['class' => 'h-5 w-5'])
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
