<div class="space-y-6 mb-8">
    <section>
        <!-- end default -->
        <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1">{{ __('events.attribute_settings') }}</h3>

        <div class="sm:flex sm:items-start space-y-4 sm:space-y-0 sm:space-x-4 mt-5">


            <div class="sm:w-1/4">
                <label class="block text-sm font-medium mb-1" for="name"> {{ __('events.attribute_name') }} <span
                        class="text-rose-500">*</span></label>
                <input type="text" name="name" id="name"
                    class="form-input w-full {{ $errors->has('name') ? 'border-rose-300' : '' }}"
                    value="{{ old('name', $attribute->name) }}" required>

                @if ($errors->has('name'))
                    <div class="text-xs mt-1 text-rose-500 h-2">
                        {{ $errors->first('name') }}
                    </div>
                @endif
            </div>


            <div class="md:w-2/4 flex flex-col gap-x-4" x-data="{ attributeType: '{{ old('attribute_type', $attribute->attribute_type ?? '') }}' }">
                <div class="w-full">
                    <label class="block text-sm font-medium mb-1" for="attribute_type">{{ __('events.attribute_type') }} <span
                            class="text-rose-500">*</span></label>
                    <select x-model="attributeType" name="attribute_type" id="attribute_type"
                        class="form-input w-full {{ $errors->has('attribute_type') ? 'border-rose-300' : '' }}"
                        required>
                        <option value="" selected disabled> {{ __('events.select_option') }} </option>
                        @foreach ($attribute_types as $attribute_type)
                            <option value="{{ $attribute_type->name }}"
                                @if (old('attribute_type', $attribute_type->name) == $attribute_type->name) selected @endif>{{ $attribute_type->value }}</option>
                        @endforeach
                    </select>

                    @if ($errors->has('attribute_type'))
                        <div class="text-xs mt-1 text-rose-500 h-2">
                            {{ $errors->first('attribute_type') }}
                        </div>
                    @endif
                </div>

                <div class="w-full" x-show="attributeType == 'SELECT'">
                    <livewire:attribute-type-select :options="old('attribute_data', $attribute->attribute_data)" />
                </div>
            </div>

            <div class="sm:w-1/4">
                <label class="block text-sm font-medium mb-1" for="default_value"> {{ __('events.default_value') }}</label>
                <input type="text" name="default_value" id="default_value"
                    class="form-input w-full {{ $errors->has('default_value') ? 'border-rose-300' : '' }}"
                    value="{{ old('default_value', $attribute->default_value) }}">

                @if ($errors->has('default_value'))
                    <div class="text-xs mt-1 text-rose-500 h-2">
                        {{ $errors->first('default_value') }}
                    </div>
                @endif
            </div>


            <div class="sm:w-1/4">

                <label class="block text-sm font-medium mb-1" for="enrollment_type">{{ __('events.enrollment_role') }}</label>
                <select name="enrollment_type" id="enrollment_type" class="form-select w-full md:h-30">

                    @foreach (\App\Enums\EvtEventEnrollmentRoleEnum::cases() as $enrollmentType)
                        <option
                            {{ old('enrollment_type', $attribute->enrollment_type) == $enrollmentType->value ? 'selected' : '' }}
                            value="{{ $enrollmentType->value }}">
                            {{ $enrollmentType->value }}
                        </option>
                    @endforeach
                </select>

                @if ($errors->has('enrollment_type'))
                    <div class="text-xs mt-1 text-rose-500 h-2">
                        {{ $errors->first('enrollment_type') }}
                    </div>
                @endif
            </div>

        </div>

        <div class="sm:flex sm:items-start space-y-4 sm:space-y-0 sm:space-x-4 mt-5">

            <div class="sm:w-1/4">
                <label class="block text-sm font-medium mb-1" for="validation_rules"> {{ __('events.validation_rules') }}
                </label>
                <input type="text" name="validation_rules" id="validation_rules"
                    class="form-input w-full {{ $errors->has('validation_rules') ? 'border-rose-300' : '' }}"
                    value="{{ old('validation_rules', $attribute->validation_rules) }}">

                @if ($errors->has('validation_rules'))
                    <div class="text-xs mt-1 text-rose-500 h-2">
                        {{ $errors->first('validation_rules') }}
                    </div>
                @endif
            </div>

            <div class="sm:w-1/3">
                <label class="block text-sm font-medium mb-1" for="custom_class"> {{ __('events.custom_class') }} </label>
                <input type="text" name="custom_class" id="custom_class"
                    class="form-input w-full {{ $errors->has('custom_class') ? 'border-rose-300' : '' }}"
                    value="{{ old('custom_class', $attribute->custom_class) }}">

                @if ($errors->has('custom_class'))
                    <div class="text-xs mt-1 text-rose-500 h-2">
                        {{ $errors->first('custom_class') }}
                    </div>
                @endif
            </div>

            <div class="sm:w-1/3">
                <label class="block text-sm font-medium mb-1" for="fillable_type">{{ __('events.fillable_type') }} <span
                        class="text-rose-500">*</span></label>
                <select name="fillable_type" id="fillable_type"
                    class="form-input w-full {{ $errors->has('fillable_type') ? 'border-rose-300' : '' }}" required>
                    <option value="" selected disabled> {{ __('events.select_option') }} </option>
                    @foreach ($attribute_fillable_types as $fillable_type)
                        <option value="{{ $fillable_type->name }}" @if (old('fillable_type', $attribute->fillable_type) == $fillable_type->name) selected @endif>
                            {{ $fillable_type->value }}</option>
                    @endforeach
                </select>

                @if ($errors->has('fillable_type'))
                    <div class="text-xs mt-1 text-rose-500 h-2">
                        {{ $errors->first('fillable_type') }}
                    </div>
                @endif
            </div>

            <div class="sm:w-1/3">
                <label class="block text-sm font-medium mb-1" for="required">{{ __('events.required_field') }}</label>
                <div class="mt-2">
                    <label class="inline-flex items-center">
                        <input type="checkbox" id="required" name="required" value="1"
                            {{ old('required', $attribute->required) ? 'checked' : '' }}
                            class="{{ $errors->has('required') ? 'border-rose-300' : '' }}">
                        <span class="ml-2">{{ __('events.attribute_must_be_filled') }}</span>
                    </label>
                    <p class="text-xs text-gray-400 mt-1">{{ __('events.attribute_required_help') }}</p>
                </div>
                @if ($errors->has('required'))
                    <div class="text-xs mt-1 text-rose-500 h-2">
                        {{ $errors->first('required') }}
                    </div>
                @endif
            </div>


        </div>

    </section>
</div>

<x-forms.card-form-submit backRoute="admin.evt-events.events.index" :buttonText="__('events.save_record')" />
