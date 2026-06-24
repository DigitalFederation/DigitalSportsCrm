<div>

    @if(!empty($old) && $old->hasMedia('profile'))
        @php
            $imageUrl = $old->getFirstMediaUrl('profile', 'thumb') ?: $old->getFirstMediaUrl('profile');
        @endphp
        @if($imageUrl)
            <img id="preview_image" src="{{ $imageUrl }}"
                 class="rounded-full h-24 w-24 object-cover"
                 onerror="this.onerror=null; this.src='{{ asset('img/user_placeholder.png') }}';">
        @else
            <img id="preview_image" src="{{ asset('img/user_placeholder.png') }}"
                 class="rounded-full h-24 w-24 object-cover" />
        @endif
    @else
        <img id="preview_image" src="{{ asset('img/user_placeholder.png') }}"
             class="rounded-full h-24 w-24 object-cover" />
    @endif

</div>
<div class="sm:w-1/3">
    <label class="block text-sm font-medium mb-1" for="logo">
        {{ $label }}
        @if($required ?? false)
            <span class="text-rose-500">*</span>
        @endif
    </label>
    <input onchange="document.getElementById('preview_image').src = window.URL.createObjectURL(this.files[0])"
           name="logo"
           class="relative m-0 block w-full min-w-0 flex-auto rounded border border-solid border-neutral-300 bg-clip-padding px-3 text-base font-normal text-neutral-700 transition duration-300 ease-in-out file:-mx-3 file:py-2 file:overflow-hidden file:rounded-none file:border-0 file:border-solid file:border-inherit file:bg-neutral-100 file:px-3 file:text-neutral-700 file:transition file:duration-150 file:ease-in-out file:[margin-inline-end:0.75rem] file:[border-inline-end-width:1px] hover:file:bg-neutral-200 focus:border-primary focus:text-neutral-700 focus:shadow-[0_0_0_1px] focus:shadow-primary focus:outline-none dark:border-neutral-600 dark:text-neutral-200 dark:file:bg-neutral-700 dark:file:text-neutral-100"
           type="file"
           {{ ($required ?? false) ? 'required' : '' }} />
    <div class="text-xs mt-1"> {{ __('main.only_jpg_png') }} </div>
    @if($errors->has('logo'))
        <div class="text-xs mt-1 text-rose-500 h-2">
            {{ $errors->first('logo') }}
        </div>
    @endif
</div>
