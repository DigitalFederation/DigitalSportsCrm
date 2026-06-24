<div>
    <div class="card md:-mr-px mb-8 w-full">
        <form wire:submit="save">

            <div>
                <input type="hidden" wire:model.live="model" value="$model">

                <div class="flex justify-start items-start">

                    <div class="w-96">
                        <input
                        class="relative m-0 block w-full min-w-0 flex-auto rounded border border-solid border-neutral-300 bg-clip-padding px-3 text-base font-normal text-neutral-700 transition duration-300 ease-in-out file:-mx-3 file:py-2 file:overflow-hidden file:rounded-none file:border-0 file:border-solid file:border-inherit file:bg-neutral-100 file:px-3 file:text-neutral-700 file:transition file:duration-150 file:ease-in-out file:[margin-inline-end:0.75rem] file:[border-inline-end-width:1px] hover:file:bg-neutral-200 focus:border-primary focus:text-neutral-700 focus:shadow-[0_0_0_1px] focus:shadow-primary focus:outline-none dark:border-neutral-600 dark:text-neutral-200 dark:file:bg-neutral-700 dark:file:text-neutral-100"
                        type="file"
                        wire:model.live="attachments"
                        multiple/>
                    </div>

                    @error('attachments.*') <span class="error">{{ $message }}</span> @enderror
                    <button type="button" wire:click="save" class="btn btn-action ml-2">Save Attachment</button>
                </div>

                <div wire:loading wire:target="save">
                    Processing file upload...
                </div>

                <div>{{ $message }}</div>
            </div>



        </form>
    </div>

    <h2 class="font-bold">{{ __('Uploaded Files') }}</h2>
    <div class="sm:flex sm:space-x-4">


        @if(!empty($files))
            <table class="table-auto w-full bg-white">

                <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                <tr>
                    <th class="py-2 px-4 text-left"> {{ __('Filename') }} </th>
                    <th class="py-2 px-2 text-left"> {{ __('Date') }} </th>
                    <th class="py-2 px-4 text-right"></th>
                </tr>
                </thead>

                <tbody class="text-sm divide-y divide-slate-200">
                @foreach($files as $document)
                    <tr>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 break-words text-left">
                            {{ $document->file_name }}
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-left">
                            <div>{{ \Carbon\Carbon::parse($document->created_at)->format('d-m-Y') }}</div>
                            <small>({{ \Carbon\Carbon::parse($document->created_at)->diffForHumans() }})</small>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 break-words text-right flex justify-end">
                            <form action="{{ route('admin.media.download') }}" method="POST" enctype="multipart/form-data" class="w-auto">
                                @csrf
                                <input type="hidden" name="id" value="{{ $document->id }}">
                                <button type="submit" class="btn-sm border-slate-200 hover:border-slate-300 shadow-sm">
                                    <div class="flex items-center">
                                        {{ __('Download') }}
                                    </div>
                                </button>
                            </form>

                            <form action="{{ route('admin.media.delete') }}" method="POST" enctype="multipart/form-data" class="ml-4 w-auto">
                            @csrf

                                <input type="hidden" name="id" value="{{ $document->id }}">
                                <button type="submit" class="btn-sm bg-red-500 border-red-200 hover:border-red-300 text-white shadow-sm">
                                    <div class="flex items-center">
                                        {{ __('Delete') }}
                                    </div>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>

            </table>
        @endif


    </div>
</div>
