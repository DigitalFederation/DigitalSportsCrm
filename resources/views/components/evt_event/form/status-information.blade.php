<div x-data="{ statusClass: '{{ class_basename($event->status_class) }}' }">
    <section class="card">

        <div class="flex gap-x-2 items-center border-b border-gray-300 pb-2 mb-4">
            <x-svg.info class="w-6 h-6 text-slate-600" />
            <span class="font-bold">{{ __('Event Status') }}</span>
        </div>

        <div class="w-full">

            <div class="form-check mb-3">
                <input type="checkbox" name="is_visible" id="is_visible"
                       value="1" {{ old('is_visible', $event->is_visible) ? 'checked' : '' }}>
                <label for="is_visible"> {{ __('Visible in Listings') }}</label>
                <p class="text-xs text-gray-400">
                    {{ __('If checked, the event will be visible in the public listings.') }}
                </p>
            </div>

            <label class="block text-sm font-medium mb-1" for="status_class">
                {{ __('Event State') }} <span class="text-rose-500">*</span>
            </label>
            <select name="status_class"
                    id="status_class"
                    class="form-input w-full"
                    x-model="statusClass"
                    @change="statusClass = $event.target.value"
                    required>
                <option value="" disabled selected> {{ __('-- Select an option --') }} </option>
                <option
                    value="PreparationEventState" {{ class_basename($event->status_class) == 'PreparationEventState' ? 'selected' : '' }}>
                    Preparation
                </option>
                <option
                    value="ActiveEventState" {{ class_basename($event->status_class) == 'ActiveEventState' ? 'selected' : '' }}>
                    Active
                </option>
                <option
                    value="ArchiveEventState" {{ class_basename($event->status_class) == 'ArchiveEventState' ? 'selected' : '' }}>
                    Archived
                </option>
            </select>
            <p class="text-xs text-gray-400">
                The current state of the event.</p>
            @if($errors->has('status_class'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('status_class') }}
                </div>
            @endif
        </div>

        <div class="mt-4 w-full"
             x-show="statusClass === 'ActiveEventState'"
             x-transition
             x-data="{ 
                organizerType: '{{ old('organizer_type', optional($event->organizer)->organizable_type ? class_basename(optional($event->organizer)->organizable_type) : '') }}',
                organizerId: '{{ old('organizer_id', '') }}'
             }">
            <label for="organizer_type" class="block text-sm font-medium mb-1">{{ __('Organizer Type') }}</label>
            <select id="organizer_type" class="form-input w-full mb-2"
                    x-model="organizerType"
                    @change="organizerId = ''">
                <option value="">{{ __('-- Select Type --') }}</option>
                <option value="Federation">{{ __('Federation') }}</option>
                <option value="Entity">{{ __('Entity') }}</option>
            </select>
            
            <div x-show="organizerType === 'Federation'" x-transition>
                <label for="organizer_id_federation" class="block text-sm font-medium mb-1">{{ __('Select Federation') }}</label>
                <select id="organizer_id_federation" 
                        class="form-input w-full" 
                        x-model="organizerId"
                        @change="$el.form.querySelector('[name=organizer_id]').value = organizerId">
                    <option value="">{{ __('-- Select Federation --') }}</option>
                    @foreach($federations as $id => $name)
                        <option value="federation_{{ $id }}"
                                @if(old('organizer_id') == "federation_{$id}" || 
                                   (optional($event->organizer)->organizable_type == 'Domain\Federations\Models\Federation' && 
                                    optional($event->organizer)->organizable_id == $id)) selected @endif>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div x-show="organizerType === 'Entity'" x-transition>
                <label for="organizer_id_entity" class="block text-sm font-medium mb-1">{{ __('Select Entity') }}</label>
                <select id="organizer_id_entity" 
                        class="form-input w-full" 
                        x-model="organizerId"
                        @change="$el.form.querySelector('[name=organizer_id]').value = organizerId">
                    <option value="">{{ __('-- Select Entity (Club, School, Diving Center) --') }}</option>
                    @if(isset($entities_list))
                        @foreach($entities_list as $id => $name)
                            <option value="entity_{{ $id }}"
                                    @if(old('organizer_id') == "entity_{$id}" ||
                                       (optional($event->organizer)->organizable_type == 'Domain\Entities\Models\Entity' && 
                                        optional($event->organizer)->organizable_id == $id)) selected @endif>{{ $name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            
            <!-- Hidden input that gets the actual value submitted -->
            <input type="hidden"
                   name="organizer_id"
                   x-bind:value="organizerId">
        </div>

    </section>

</div>
