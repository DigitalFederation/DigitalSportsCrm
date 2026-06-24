<div>
    <!-- Form for document upload -->
    <div class="card md:-mr-px mb-8 w-full">
        <!-- Error messages -->
        @if (!empty($message))
            <div class="text-red-700">
                {{ $message }}
            </div>
        @endif

        <form wire:submit.prevent="save" class="flex flex-col gap-2 justify-start">
            <!-- File input -->
            <div class="w-full">

                <div>
                    <label class="block text-sm font-medium" for="attachments">{{ __('File to upload') }} <span class="text-red-500">*</span></label>
                    <input
                        id="attachments"
                        class="relative m-0 block w-full min-w-0 flex-auto rounded border border-solid border-neutral-300 bg-clip-padding px-3 text-base font-normal text-neutral-700 transition duration-300 ease-in-out file:-mx-3 file:py-2 file:overflow-hidden file:rounded-none file:border-0 file:border-solid file:border-inherit file:bg-neutral-100 file:px-3 file:text-neutral-700 file:transition file:duration-150 file:ease-in-out file:[margin-inline-end:0.75rem] file:[border-inline-end-width:1px] hover:file:bg-neutral-200 focus:border-primary focus:text-neutral-700 focus:shadow-[0_0_0_1px] focus:shadow-primary focus:outline-none dark:border-neutral-600 dark:text-neutral-200 dark:file:bg-neutral-700 dark:file:text-neutral-100"
                        type="file" wire:model="attachments" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" />
                    <div wire:loading wire:target="attachments" class="text-sm text-blue-600 mt-1">
                        {{ __('Uploading...') }}
                    </div>
                    @error('attachments')
                        <span class="error">{{ $message }}</span>
                    @enderror
                    
                    @if($attachments)
                        <div class="text-sm text-green-600 mt-1">
                            @if(is_array($attachments))
                                {{ count($attachments) }} {{ __('file(s) selected') }}
                            @else
                                1 {{ __('file selected') }}
                            @endif
                        </div>
                    @endif
                </div>

            </div>

            <div class="w-full grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end mt-2">


                <div class="flex flex-row">

                    <!-- Document type dropdown -->
                    <div class="flex flex-col">
                        <label>{{ __('Select Type') }} <span class="text-red-500">*</span></label>
                        <select wire:model="type" required class="form-select w-full">
                            <option value="" selected disabled>{{ __('-- Document type --') }}</option>
                            @foreach ($types as $type)
                                <option value="{{ $type }}">
                                    {{ \App\Enums\OfficialDocumentTypeEnum::toString($type) }} </option>
                            @endforeach
                        </select>
                        @error('type')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>



                </div>


                <!-- Issue Date field -->
                @php
                    $issueDateRequired = $type === \App\Enums\OfficialDocumentTypeEnum::ADELCertificate->value || in_array($role, ['coach', 'diving-professional']);
                @endphp
                <div class="flex flex-col">
                    <label>{{ __('Issue Date') }} @if($issueDateRequired)<span class="text-red-500">*</span>@endif</label>
                    <input type="date" wire:model="issue_date" class="form-input w-full" @if($issueDateRequired) required @endif />
                    @error('issue_date')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Expiry Date field -->
                <div class="flex flex-col">
                    <label>{{ __('Expiration Date') }}</label>
                    <input type="date" wire:model="expiry_date" class="form-input w-full" />
                    @error('expiry_date')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Submit button (moved up in the DOM for better tab order) -->
                <button type="submit" class="btn btn-primary w-full md:col-span-2 lg:col-span-1" wire:loading.attr="disabled" wire:target="save, attachments">
                    <span wire:loading.remove wire:target="save">{{ __('Submeter Documento') }}</span>
                    <span wire:loading wire:target="save">{{ __('Uploading...') }}</span>
                </button>


            </div>

        </form>
    </div>
</div>
