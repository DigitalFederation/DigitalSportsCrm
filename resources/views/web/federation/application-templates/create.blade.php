<x-layout>
    <div class="space-y-6" x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 100)">

        {{-- Header Card --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden"
             x-show="loaded"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform -translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0">

            <div class="px-6 py-6 sm:px-8">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full bg-indigo-100">
                            <x-heroicon-s-document-plus class="w-6 h-6 text-indigo-600" />
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200">
                            {{ __('event_applications.application_template') }}
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">{{ __('event_applications.titles.create_template') }}</h1>
                    <p class="mt-1 text-gray-500 text-sm">{{ __('event_applications.header.create_template_subtitle') }}</p>
                </div>

                <div class="mt-5 pt-4 border-t border-gray-100 flex items-center gap-4">
                    <a href="{{ route($backRoute) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-primary-light rounded-lg font-medium text-sm text-primary tracking-wide shadow-sm hover:bg-secondary-light focus:outline-none focus:border-primary focus:ring focus:ring-primary-light/30 transition-colors duration-150">
                        <x-heroicon-m-arrow-left class="w-4 h-4" />
                        {{ __('event_applications.actions.back_to_templates') }}
                    </a>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route($routeNamespace . '.application-templates.store') }}">
            @csrf

            <div class="space-y-6">

                {{-- Card 1: General Information --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden"
                     x-show="loaded"
                     x-transition:enter="transition ease-out duration-500 delay-100"
                     x-transition:enter-start="opacity-0 transform translate-y-4"
                     x-transition:enter-end="opacity-100 transform translate-y-0">

                    <div class="border-b border-gray-200 bg-gray-50/50 px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-100">
                                <x-heroicon-o-information-circle class="w-4 h-4 text-indigo-600" />
                            </div>
                            <span class="text-sm font-semibold text-gray-700">{{ __('event_applications.sections.general_information') }}</span>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium mb-1" for="name">
                                    {{ __('event_applications.labels.template_name') }} <span class="text-rose-500">*</span>
                                </label>
                                <input type="text"
                                       id="name"
                                       name="name"
                                       class="form-input w-full @error('name') border-rose-300 @enderror"
                                       value="{{ old('name') }}"
                                       placeholder="{{ __('event_applications.placeholders.template_name') }}"
                                       required>
                                @error('name')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1" for="event_type">
                                    {{ __('event_applications.labels.event_type') }} <span class="text-rose-500">*</span>
                                </label>
                                <select id="event_type"
                                        name="event_type"
                                        class="form-select w-full @error('event_type') border-rose-300 @enderror"
                                        required>
                                    <option value="">{{ __('common.select') }}</option>
                                    <option value="organization" {{ old('event_type') === 'organization' ? 'selected' : '' }}>
                                        {{ __('event_applications.event_types.organization') }}
                                    </option>
                                    <option value="competition" {{ old('event_type') === 'competition' ? 'selected' : '' }}>
                                        {{ __('event_applications.event_types.competition') }}
                                    </option>
                                </select>
                                @error('event_type')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1" for="sport_id">
                                    {{ __('event_applications.labels.sport') }}
                                </label>
                                <select id="sport_id"
                                        name="sport_id"
                                        class="form-select w-full @error('sport_id') border-rose-300 @enderror">
                                    <option value="">{{ __('common.select') }}</option>
                                    @foreach($sports as $sport)
                                        <option value="{{ $sport->id }}" {{ old('sport_id') == $sport->id ? 'selected' : '' }}>
                                            {{ $sport->translated_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('sport_id')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1" for="registration_type">
                                    {{ __('event_applications.labels.registration_type') }}
                                </label>
                                <select id="registration_type"
                                        name="registration_type"
                                        class="form-select w-full @error('registration_type') border-rose-300 @enderror">
                                    <option value="">{{ __('common.select') }}</option>
                                    @foreach(['entities', 'entities_individuals', 'individuals', 'federations', 'federations_entities', 'federations_entities_individuals'] as $type)
                                        <option value="{{ $type }}" {{ old('registration_type') === $type ? 'selected' : '' }}>
                                            {{ __('event_applications.registration_types.' . $type) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('registration_type')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1" for="category">
                                    {{ __('event_applications.labels.category') }}
                                </label>
                                <select id="category"
                                        name="category"
                                        class="form-select w-full @error('category') border-rose-300 @enderror">
                                    <option value="">{{ __('common.select') }}</option>
                                    @foreach(['A', 'B', 'C', 'D'] as $cat)
                                        <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>
                                            {{ __('event_applications.categories.' . $cat) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1" for="age_group">
                                    {{ __('event_applications.labels.age_group') }}
                                </label>
                                <input type="text"
                                       id="age_group"
                                       name="age_group"
                                       class="form-input w-full @error('age_group') border-rose-300 @enderror"
                                       value="{{ old('age_group') }}">
                                @error('age_group')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium mb-1" for="description">
                                    {{ __('event_applications.labels.description') }}
                                </label>
                                <textarea id="description"
                                          name="description"
                                          rows="4"
                                          class="form-textarea w-full @error('description') border-rose-300 @enderror"
                                          placeholder="{{ __('event_applications.placeholders.template_name') }}">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 2: Submission Period --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden"
                     x-show="loaded"
                     x-transition:enter="transition ease-out duration-500 delay-150"
                     x-transition:enter-start="opacity-0 transform translate-y-4"
                     x-transition:enter-end="opacity-100 transform translate-y-0">

                    <div class="border-b border-gray-200 bg-gray-50/50 px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-100">
                                <x-heroicon-o-calendar-days class="w-4 h-4 text-blue-600" />
                            </div>
                            <span class="text-sm font-semibold text-gray-700">{{ __('event_applications.sections.submission_period') }}</span>
                        </div>
                    </div>

                    <div class="p-6">
                        <p class="text-sm text-slate-500 mb-4">{{ __('event_applications.help.submission_period') }}</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1" for="submission_start_date">
                                    {{ __('event_applications.labels.submission_start_date') }} <span class="text-rose-500">*</span>
                                </label>
                                <input type="date"
                                       id="submission_start_date"
                                       name="submission_start_date"
                                       class="form-input w-full @error('submission_start_date') border-rose-300 @enderror"
                                       value="{{ old('submission_start_date') }}"
                                       required>
                                <p class="text-xs text-slate-500 mt-1">{{ __('event_applications.help.submission_start_date') }}</p>
                                @error('submission_start_date')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1" for="submission_end_date">
                                    {{ __('event_applications.labels.submission_end_date') }} <span class="text-rose-500">*</span>
                                </label>
                                <input type="date"
                                       id="submission_end_date"
                                       name="submission_end_date"
                                       class="form-input w-full @error('submission_end_date') border-rose-300 @enderror"
                                       value="{{ old('submission_end_date') }}"
                                       required>
                                <p class="text-xs text-slate-500 mt-1">{{ __('event_applications.help.submission_end_date') }}</p>
                                @error('submission_end_date')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 3: Event Period --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden"
                     x-show="loaded"
                     x-transition:enter="transition ease-out duration-500 delay-200"
                     x-transition:enter-start="opacity-0 transform translate-y-4"
                     x-transition:enter-end="opacity-100 transform translate-y-0">

                    <div class="border-b border-gray-200 bg-gray-50/50 px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-100">
                                <x-heroicon-o-clock class="w-4 h-4 text-emerald-600" />
                            </div>
                            <span class="text-sm font-semibold text-gray-700">{{ __('event_applications.sections.event_period') }}</span>
                        </div>
                    </div>

                    <div class="p-6">
                        <p class="text-sm text-slate-500 mb-4">{{ __('event_applications.help.event_period') }}</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1" for="event_start_date">
                                    {{ __('event_applications.labels.event_start_date') }}
                                </label>
                                <input type="date"
                                       id="event_start_date"
                                       name="event_start_date"
                                       class="form-input w-full @error('event_start_date') border-rose-300 @enderror"
                                       value="{{ old('event_start_date') }}">
                                <p class="text-xs text-slate-500 mt-1">{{ __('event_applications.help.event_start_date') }}</p>
                                @error('event_start_date')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1" for="event_end_date">
                                    {{ __('event_applications.labels.event_end_date') }}
                                </label>
                                <input type="date"
                                       id="event_end_date"
                                       name="event_end_date"
                                       class="form-input w-full @error('event_end_date') border-rose-300 @enderror"
                                       value="{{ old('event_end_date') }}">
                                <p class="text-xs text-slate-500 mt-1">{{ __('event_applications.help.event_end_date') }}</p>
                                @error('event_end_date')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1" for="max_applications">
                                    {{ __('event_applications.labels.max_applications') }}
                                </label>
                                <input type="number"
                                       id="max_applications"
                                       name="max_applications"
                                       min="1"
                                       class="form-input w-full @error('max_applications') border-rose-300 @enderror"
                                       value="{{ old('max_applications') }}">
                                <p class="text-xs text-slate-500 mt-1">{{ __('event_applications.help.max_applications') }}</p>
                                @error('max_applications')
                                    <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 4: Settings --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden"
                     x-show="loaded"
                     x-transition:enter="transition ease-out duration-500 delay-[250ms]"
                     x-transition:enter-start="opacity-0 transform translate-y-4"
                     x-transition:enter-end="opacity-100 transform translate-y-0">

                    <div class="border-b border-gray-200 bg-gray-50/50 px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-amber-100">
                                <x-heroicon-o-cog-6-tooth class="w-4 h-4 text-amber-600" />
                            </div>
                            <span class="text-sm font-semibold text-gray-700">{{ __('event_applications.sections.settings') }}</span>
                        </div>
                    </div>

                    <div class="p-6">
                        <div>
                            <label class="block text-sm font-medium mb-2">
                                {{ __('event_applications.template_target_audience.label') }} <span class="text-rose-500">*</span>
                            </label>
                            <p class="text-xs text-slate-500 mb-2">{{ __('event_applications.template_target_audience.help') }}</p>
                            <div class="space-y-2">
                                @foreach(['both', 'entities', 'federations'] as $audience)
                                    <label class="flex items-center">
                                        <input type="radio"
                                               name="target_audience"
                                               value="{{ $audience }}"
                                               class="form-radio"
                                               {{ old('target_audience', 'both') === $audience ? 'checked' : '' }}>
                                        <span class="text-sm ml-2">{{ __('event_applications.template_target_audience.' . $audience) }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('target_audience')
                                <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Footer Action Bar --}}
                <div class="bg-gradient-to-r from-slate-50 to-gray-50 rounded-xl border border-gray-200 overflow-hidden"
                     x-show="loaded"
                     x-transition:enter="transition ease-out duration-500 delay-300"
                     x-transition:enter-start="opacity-0 transform translate-y-4"
                     x-transition:enter-end="opacity-100 transform translate-y-0">
                    <div class="p-4 sm:p-6">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 p-2 rounded-lg bg-white border border-gray-200 shadow-sm">
                                    <x-heroicon-o-check-circle class="w-5 h-5 text-gray-600" />
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-900">{{ __('event_applications.footer.ready_to_save') }}</h3>
                                    <p class="mt-0.5 text-sm text-gray-500">{{ __('event_applications.footer.review_before_saving') }}</p>
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                                <a href="{{ route($backRoute) }}"
                                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-200">
                                    {{ __('common.cancel') }}
                                </a>
                                <button type="submit"
                                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg shadow-sm hover:from-orange-600 hover:to-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition-all duration-200">
                                    <x-heroicon-m-check class="w-4 h-4" />
                                    {{ __('common.save') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>

    </div>
</x-layout>
