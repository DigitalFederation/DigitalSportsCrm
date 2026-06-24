@if(isset($refereeAttributes) && $refereeAttributes->count() > 0)
<div>
    <h4 class="text-sm font-medium mb-2">{{ __('events.form.referee_attributes') }}</h4>
    <div class="w-full overflow-auto max-h-48 border border-slate-200 rounded-lg p-2">
        @foreach ($refereeAttributes as $attribute)
            <div>
                <label class="flex justify-between cursor-pointer py-1">
                    <span class="text-sm">{{ $attribute->name }}</span>
                    <input @if (isset($event) && $event->refereeAttributes->contains($attribute->id)) checked @endif
                        type="checkbox"
                        value="{{ $attribute->id }}"
                        id="referee_attribute_{{ $attribute->id }}"
                        name="selected_referee_attributes[]"
                        class="form-checkbox h-4 w-4 text-indigo-600 transition duration-150 ease-in-out" />
                </label>
            </div>
        @endforeach
    </div>
    <p class="text-xs text-slate-500 mt-1">{{ __('events.form.referee_attributes_hint') }}</p>
</div>
@endif
