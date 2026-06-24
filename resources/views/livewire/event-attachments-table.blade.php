<div class="card mb-4">
    <div class="flex gap-x-2 items-center border-b border-gray-300 pb-2 mb-4">
        <x-svg.box-arrow-down class="w-6 h-6 text-slate-600" />
        <span class="font-bold">{{ __('events.attachments.title') }}</span>
    </div>

    <!-- File Upload Section -->
    <x-information-box
        title=""
        :body="__('events.attachments.upload_info')"
    ></x-information-box>

    <!-- File Size Notice -->
    <div class="mt-2 mb-3 p-2 bg-amber-50 border border-amber-200 rounded-md">
        <p class="text-sm text-amber-800 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>
                <strong>{{ __('events.attachments.upload_limit') }}:</strong> {{ __('events.attachments.upload_limit_message') }}
            </span>
        </p>
    </div>

    <div class="mt-4 mb-4 rounded-md border border-slate-300 p-2 flex flex-row justify-start items-center gap-4">

        <div class="w-full md:w-1/3">
            <label for="newAttachment" class="block text-sm font-medium mb-1">
                {{ __('events.attachments.browse_file') }} <span class="text-xs text-gray-500">({{ __('events.attachments.max_size') }}: 80MB)</span>
            </label>
            <div x-data="{
                uploading: false,
                progress: 0,
                fileError: false,
                errorMessage: ''
            }" x-on:livewire-upload-start="uploading = true; fileError = false"
                x-on:livewire-upload-finish="uploading = false"
                x-on:livewire-upload-error="uploading = false; fileError = true; errorMessage = '{{ __('events.attachments.upload_failed') }}'"
                x-on:livewire-upload-progress="progress = $event.detail.progress">
                <input id="newAttachment"
                    class="border border-slate-100 w-full file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold cursor-pointer"
                    type="file" wire:model="newAttachment">

                <!-- Upload Progress Bar -->
                <div x-show="uploading" class="mt-2">
                    <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-500 transition-all duration-300" :style="`width: ${progress}%`">
                        </div>
                    </div>
                    <p class="text-xs text-gray-600 mt-1">{{ __('events.attachments.uploading') }}: <span x-text="progress"></span>%</p>
                </div>

                <!-- Error Message -->
                <div x-show="fileError" class="mt-2">
                    <p class="text-xs text-red-500" x-text="errorMessage"></p>
                </div>

                @error('newAttachment')
                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="w-full md:w-1/3">
            <label for="newAttachmentName" class="block text-sm font-medium mb-1">
                {{ __('events.attachments.file_name_optional') }}
            </label>
            <input type="text" id="newAttachmentName" wire:model="newAttachmentName" class="form-input w-full">
            @error('newAttachmentName')
                <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>
        <button wire:click="saveAttachment" class="btn btn-info mt-5" type="button">{{ __('events.attachments.upload_file') }}</button>
    </div>

    <!-- Attachments Section -->
    <div class="space-y-6">
        @if ($generalAttachments->count() > 0)
            <div class="mb-4">
                <div class="flex justify-between items-center mb-3">
                    <span class="font-bold">{{ __('events.attachments.general_attachments') }}</span>
                    <button wire:click="downloadAllAttachments('event-general-attachments')"
                        class="btn-sm btn-outline text-blue-600 flex items-center gap-2">
                        <x-svg.box-arrow-down class="w-4 h-4" />
                        {{ __('events.attachments.download_all') }} ({{ $generalAttachments->count() }})
                    </button>
                </div>
                <x-dynamic-table :headers="[__('events.attachments.date_uploaded'), __('events.attachments.name'), __('events.attachments.type'), __('events.attachments.size'), '']">
                    @foreach ($generalAttachments as $attachment)
                        <tr class="hover:bg-gray-100">
                            <td class="pl-5 text-sm">{{ $attachment->created_at->format('d/m/Y') }}</td>
                            <td class="pl-5 text-sm">{{ $attachment->name }}</td>
                            <td class="py-1 text-sm">{{ getSimplifiedFileType($attachment->mime_type) }}</td>
                            <td class="py-1 text-sm">{{ formatBytes($attachment->size) }}</td>
                            <td class="py-1 flex items-center justify-end gap-x-2">

                                <button type="button" wire:click="downloadAttachment({{ $attachment->id }})"
                                    class="btn btn-sm btn-outline text-blue-600">
                                    <x-svg.box-arrow-down class="w-4 h-4" />
                                </button>

                                <button type="button" wire:click="confirmDeleteAttachment({{ $attachment->id }})"
                                    class="btn btn-sm btn-outline text-red-500">
                                    <x-svg.trash class="w-4 h-4" />
                                </button>

                            </td>
                        </tr>
                    @endforeach
                </x-dynamic-table>
            </div>
        @endif

        @if ($generalAttachments->count() > 0)
            <div class="mt-6 pt-4 border-t border-gray-200">
                <div class="flex justify-end">
                    <button wire:click="downloadAllAttachments" class="btn-primary flex items-center gap-2">
                        <x-svg.box-arrow-down class="w-5 h-5" />
                        {{ __('events.attachments.download_all_attachments') }}
                        ({{ $generalAttachments->count() }})
                    </button>
                </div>
            </div>
        @endif
    </div>

    <!-- Confirmation Modal -->
    <x-livewire-confirmation-modal :isOpen="$confirmingAttachmentDeletion" :title="__('common.warning')"
        :message="__('events.attachments.confirm_delete')" confirmMethod="deleteAttachment" :confirmText="__('common.delete')"
        cancelMethod="$set('confirmingAttachmentDeletion', false)" :cancelText="__('common.no')" />

</div>
