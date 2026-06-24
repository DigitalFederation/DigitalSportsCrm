<div>
    <form wire:submit.prevent="submitForm">

        <section>
            <div class="sm:flex flex-wrap sm:items-center gap-4">
                <h3 class="font-bold text-lg text-slate-700">{{ __('Details') }}</h3>

                <!-- General info and location -->
                <div class="flex flex-col w-full gap-4">
                    <div class="w-full">
                        <label class="block text-sm font-medium mb-1"> {{ __('Notes') }} </label>

                        <p class="text-sm">{{$event->notes}}</p>
                    </div>

                    <div class="w-full">
                        <label class="block text-sm font-medium mb-1"
                               for="description"> {{ __('Description') }} </label>

                        <textarea
                            name="description"
                            id="description"
                            wire:model="description"
                            class="form-input w-full {{ $errors->has('description') ? 'border-rose-300' : '' }}"
                            rows="5">{{old('description',$event->description)}}</textarea>

                        @if($errors->has('description'))
                            <div class="text-xs mt-1 text-rose-500 h-2">
                                {{ $errors->first('description') }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex flex-col md:flex-row w-full gap-4">
                    <div class="md:w-1/2">
                        <!-- Location -->
                        <label class="block text-sm font-medium mb-1" for="location"> {{ __('Location') }}</label>
                        <input
                            type="text"
                            name="location"
                            id="location"
                            wire:model="location"
                            class="form-input w-full {{ $errors->has('location') ? 'border-rose-300' : '' }}"
                            value="{{old('location',$event->location)}}">

                        @if($errors->has('location'))
                            <div class="text-xs mt-1 text-rose-500 h-2">
                                {{ $errors->first('location') }}
                            </div>
                        @endif
                    </div>

                    <div class="md:w-1/2">
                        <!-- Location -->
                        <label class="block text-sm font-medium mb-1" for="location"> {{ __('Address') }}</label>
                        <input
                            type="text"
                            name="address"
                            id="address"
                            wire:model="address"
                            class="form-input w-full {{ $errors->has('location') ? 'border-rose-300' : '' }}"
                            value="{{old('address',$event->address)}}">

                        @if($errors->has('address'))
                            <div class="text-xs mt-1 text-rose-500 h-2">
                                {{ $errors->first('address') }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex flex-col md:flex-row w-full gap-4">
                    <div class="md:w-1/4">
                        <label class="block text-sm font-medium mb-1"
                               for="start_date"> {{ __('Event Start Date') }}</label>
                        <input
                            type="date"
                            name="start_date"
                            id="start_date"
                            wire:model="start_date"
                            class="form-input w-full {{ $errors->has('start_date') ? 'border-rose-300' : '' }}"
                            value="{{old('start_date', \Carbon\Carbon::parse($event->start_date)->format('Y-m-d'))}}">
                        <p class="text-xs italic text-gray-400">The suggested date when the event should officially
                            start.</p>
                        @if($errors->has('start_date'))
                            <div class="text-xs mt-1 text-rose-500 h-2">
                                {{ $errors->first('start_date') }}
                            </div>
                        @endif
                    </div>

                    <div class="md:w-1/4">

                        <label class="block text-sm font-medium mb-1" for="end_date"> {{ __('Event End Date') }}</label>
                        <input
                            type="date"
                            name="end_date"
                            id="end_date"
                            wire:model="end_date"
                            class="form-input w-full {{ $errors->has('end_date') ? 'border-rose-300' : '' }}"
                            value="{{old('end_date', \Carbon\Carbon::parse($event->end_date))->format('Y-m-d')}}">
                        <p class="text-xs italic text-gray-400">The suggested date when the event should officially
                            end.</p>
                        @if($errors->has('end_date'))
                            <div class="text-xs mt-1 text-rose-500 h-2">
                                {{ $errors->first('end_date') }}
                            </div>
                        @endif

                    </div>

                    <div class="sm:w-1/2">
                        <label class="block text-sm font-medium mb-1" for="status_class">{{ __('Event State') }} <span
                                class="text-rose-500">*</span></label>
                        <select name="status_class"
                                id="status_class"
                                class="form-input w-full"
                                wire:model.live="status_class_selected"
                                required>
                            <option value="" selected> {{ __('-- Select an option --') }} </option>
                            <option value="{{ \Domain\EvtEvents\States\PreparationEventState::class }}">Preparation
                            </option>
                            <option value="{{ \Domain\EvtEvents\States\ActiveEventState::class }}">Active</option>
                        </select>
                        <p class="text-xs italic text-gray-400">The current state of the event.</p>
                        @if($errors->has('status_class'))
                            <div class="text-xs mt-1 text-rose-500 h-2">
                                {{ $errors->first('status_class') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <section class="my-4">
            <div class="sm:w-1/2">
                <h3 class="font-bold text-lg text-slate-700">{{ __('Featured Image') }}</h3>
                <input
                    name="featured_image" wire:model.live="featured_image"
                    class="relative m-0 block w-full min-w-0 flex-auto rounded border border-solid border-neutral-300 bg-clip-padding px-3 text-base font-normal text-neutral-700 transition duration-300 ease-in-out file:-mx-3 file:py-2 file:overflow-hidden file:rounded-none file:border-0 file:border-solid file:border-inherit file:bg-neutral-100 file:px-3 file:text-neutral-700 file:transition file:duration-150 file:ease-in-out file:[margin-inline-end:0.75rem] file:[border-inline-end-width:1px] hover:file:bg-neutral-200 focus:border-primary focus:text-neutral-700 focus:shadow-[0_0_0_1px] focus:shadow-primary focus:outline-none dark:border-neutral-600 dark:text-neutral-200 dark:file:bg-neutral-700 dark:file:text-neutral-100"
                    type="file"/>
                <div class="text-xs mt-1"> {{ __('*Only Jpg or Png files.') }} </div>
                @if($errors->has('featured_image'))
                    <div class="text-xs mt-1 text-rose-500 h-2">
                        {{ $errors->first('featured_image') }}
                    </div>
                @endif
            </div>

            <div class="sm:w-1/2 mt-1">
                @if(!empty($event->getFirstMediaUrl('featured-image')))
                    <img id="featured_image" class="object-cover"
                         src="{{ asset($event->getFirstMediaUrl('featured-image')) }}" alt="featured image"/>
                @endif
            </div>
        </section>

        <x-forms.card-form-submit backRoute="federation.evt-events.events.index" :buttonText="__('Save record')"/>
    </form>
</div>
