<div class="card overflow-hidden">

    <h3 class="font-bold text-lg text-slate-500 border-b border-slate-500 mb-4 pb-2 flex items-center">
        {{ __('events.form.individual_attributes') }}
        <sl-tooltip content="{{ __('events.form.individual_attributes_tooltip') }}">
            <sl-button>
                <x-svg.info class="h-5 w-5 text-gray-400" />
            </sl-button>
        </sl-tooltip>
    </h3>

    <div class="w-full overflow-auto">
        @foreach($allAttributes as $attribute)
            <div>
                <label class="flex justify-between cursor-pointer">
                    <span>{{ $attribute->name }}</span>

                    <input
                        @if(isset($event) && $event->attributes->contains($attribute->id)) checked @endif
                        type="checkbox"
                        value="{{ $attribute->id }}"
                        id="attribute_{{ $attribute->id }}"
                        name="selected_attributes[]"
                        class="form-checkbox h-4 w-4 text-indigo-600 transition duration-150 ease-in-out" />
                </label>
            </div>
        @endforeach
    </div>
    <p class="text-xs text-gray-400 mb-2 mt-2">
        {{ __('events.form.individual_attributes_hint') }}
    </p>
</div>
