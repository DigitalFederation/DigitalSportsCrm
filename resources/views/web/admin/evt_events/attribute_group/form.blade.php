<div class="flex gap-x-4">
    <div class="card sm:w-2/3">
        <div>
            <label for="name" class="block text-sm font-medium mb-2">{{ __('events.attribute_group_name') }}</label>

            <input
                type="text"
                name="name"
                id="name"
                class="form-input w-full"
                @if (! empty($attributeGroup))
                    value="{{ $attributeGroup->name }}"
                @endif
                required>
        </div>
        <div class="py-4">
            <h3 class="text-lg font-semibold mb-3 border-b">{{ __('events.select_attributes') }}</h3>
            @foreach ($attributes as $attribute)
                <div class="flex items-start mb-2">
                    <input type="checkbox"
                           id="attribute_{{ $attribute->id }}"
                           name="attributes[]"
                           value="{{ $attribute->id }}"
                           class="form-checkbox h-4 w-4"
                           @if (! empty($attributeGroup) && $attributeGroup->attributes->contains($attribute->id)) checked @endif
                    >
                    <label for="attribute_{{ $attribute->id }}"
                           class="ml-2 block text-sm font-medium">{{ $attribute->name }}</label>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            <x-forms.card-form-submit backRoute="admin.evt-events.attribute-group.index"
                                      :buttonText="__('events.save_group')" />
        </div>
    </div>
</div>
