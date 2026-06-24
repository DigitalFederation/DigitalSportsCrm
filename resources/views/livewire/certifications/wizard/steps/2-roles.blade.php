<div class="space-y-4">

    <div class="card mb-6">
        <h2 class="text-lg font-semibold">
            @if ($actorType === 'entity')
                {{ __('certifications.step_2_select_roles_entity') }}
            @else
                {{ __('certifications.step_2_select_roles') }}
            @endif
        </h2>
        <p class="text-sm text-gray-600">
            @if ($actorType === 'entity')
                {{ __('certifications.select_students_details_entity') }}
            @else
                {{ __('certifications.select_students_details') }}
            @endif
        </p>
    </div>

    {{-- Summary section for the whole attribution (federation only) --}}
    @if ($actorType === 'federation')
    <div class="mb-8">
        <div class="flex items-center mb-4">
            <h3 class="text-xl font-bold text-gray-900 tracking-tight">{{ __('certifications.attribution_context_summary') }}</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- School --}}
            <div class="bg-white border border-blue-100 rounded-xl shadow-sm p-6 flex flex-col items-start">
                <div class="flex items-center mb-3">
                    <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-blue-100 text-blue-700 mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v18m9-9H3" />
                        </svg>
                    </span>
                    <span class="text-lg font-semibold text-blue-900">{{ __('certifications.school') }}</span>
                </div>
                @if($selectedSchool)
                    <div>
                        <div class="text-base font-medium text-gray-900">{{ $selectedSchool->name }}</div>
                        @if($selectedSchool->member_code)
                            <div class="text-xs text-gray-500 mt-1">CMAS: {{ $selectedSchool->member_code }}</div>
                        @endif
                        @if($selectedSchool->location)
                            <div class="text-xs text-gray-400 mt-1">{{ $selectedSchool->location }}</div>
                        @endif
                    </div>
                @else
                    <div class="text-sm text-gray-400 italic">{{ __('certifications.no_school_selected') }}</div>
                @endif
            </div>

            {{-- Course Director --}}
            <div class="bg-white border border-green-100 rounded-xl shadow-sm p-6 flex flex-col items-start">
                <div class="flex items-center mb-3">
                    <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-green-100 text-green-700 mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.485 0 4.797.657 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </span>
                    <span class="text-lg font-semibold text-green-900">{{ __('certifications.course_director') }}</span>
                </div>
                @if($selectedDirector)
                    <div>
                        <div class="text-base font-medium text-gray-900">{{ $selectedDirector->full_name }}</div>
                        @if($selectedDirector->member_code)
                            <div class="text-xs text-gray-500 mt-1">CMAS: {{ $selectedDirector->member_code }}</div>
                        @endif
                        @if($selectedDirector->email)
                            <div class="text-xs text-gray-400 mt-1">{{ $selectedDirector->email }}</div>
                        @endif
                    </div>
                @else
                    <div class="text-sm text-gray-400 italic">{{ __('certifications.no_director_selected') }}</div>
                @endif
            </div>

            {{-- Assistant Instructors --}}
            <div class="bg-white border border-yellow-100 rounded-xl shadow-sm p-6 flex flex-col items-start">
                <div class="flex items-center mb-3">
                    <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-yellow-100 text-yellow-700 mr-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M16 3.13a4 4 0 010 7.75M8 3.13a4 4 0 000 7.75" />
                        </svg>
                    </span>
                    <span class="text-lg font-semibold text-yellow-900">{{ __('certifications.assistant_instructors') }}</span>
                </div>
                @if($selectedAssistants->isNotEmpty())
                    <ul class="space-y-2 w-full">
                        @foreach($selectedAssistants as $assistant)
                            <li class="flex items-center justify-between group">
                                <div>
                                    <div class="text-base font-medium text-gray-900">{{ $assistant->full_name }}</div>
                                    @if($assistant->member_code)
                                        <div class="text-xs text-gray-500">CMAS: {{ $assistant->member_code }}</div>
                                    @endif
                                    @if($assistant->email)
                                        <div class="text-xs text-gray-400">{{ $assistant->email }}</div>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-sm text-gray-400 italic">{{ __('certifications.no_assistants_selected') }}</div>
                @endif
            </div>
        </div>
    </div>
    @endif


    <div class="grid grid-cols-1 gap-6">
        <!-- Certification details -->
        <div class="space-y-4 card">
            <h3 class="font-medium text-slate-800">{{ __('certifications.certification_details') }}</h3>

            {{-- Certification Select --}}
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1"
                    for="certificationSelect">{{ __('certifications.certification') }}</label>
                <select id="certificationSelect" wire:model.live="selectedCertificationId"
                    class="form-select w-full dark:bg-gray-800 dark:border-gray-700" @disabled(!$selectedDirectorId && !$federationApprove)>
                    <option value="">{{ __('certifications.select_certification') }}</option>
                    @foreach ($certifications as $certification)
                        <option value="{{ $certification->id }}">{{ $certification->name }}</option>
                    @endforeach
                </select>
                @error('selectedCertificationId')
                    <span class="text-danger-500 text-sm mt-1">{{ $message }}</span>
                @enderror
                @if (!$selectedDirectorId && !$federationApprove)
                    <p class="text-xs text-gray-500 mt-1">
                        {{ __('certifications.please_select_director_or_ntc') }}
                    </p>
                @endif
            </div>

            {{-- Issue Date and Expiration Date --}}
            @if ($this->actorType === 'federation')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Issue Date Input --}}
                <div>
                    <label class="block text-sm font-medium mb-1" for="issueDate">{{ __('certifications.issue_date') }}</label>
                    <input type="date" id="issueDate" wire:model.live="issueDate"
                        class="form-input w-full dark:bg-gray-800 dark:border-gray-700" value="{{ old('issueDate') }}">
                    <div class="text-xs mt-1 text-gray-500 dark:text-gray-400">{{ __('certifications.whats_start_date') }}</div>
                    @error('issueDate')
                        <div class="text-xs mt-1 text-danger-500 h-2">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Expiration Date Input --}}
                <div>
                    <label class="block text-sm font-medium mb-1"
                        for="expirationDate">{{ __('certifications.expiration_date') }}</label>
                    <input type="date" id="expirationDate" wire:model.live="expirationDate"
                        class="form-input w-full dark:bg-gray-800 dark:border-gray-700"
                        value="{{ old('expirationDate') }}" />
                    <div class="text-xs mt-1 text-gray-500 dark:text-gray-400">
                        {{ __('certifications.when_certification_expires') }}</div>
                    @error('expirationDate')
                        <div class="text-xs mt-1 text-danger-500 h-2">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            @endif

            {{-- Notes Textarea --}}
            <div class="w-full">
                <label class="block text-sm font-medium mb-1" for="notes">{{ __('certifications.notes') }}</label>
                <textarea id="notes" class="form-textarea w-full dark:bg-gray-800 dark:border-gray-700" rows="2"
                    wire:model.live="notes"></textarea>
                <div class="text-xs mt-1 text-gray-500 dark:text-gray-400">
                    {{ __('certifications.add_notes_if_needed') }}</div>
                @error('notes')
                    <div class="text-xs mt-1 text-danger-500 h-2">{{ $message }}</div>
                @enderror
            </div>

            {{-- Summary info for selected certification --}}
            @if ($selectedCertification)
                <div class="p-3 border rounded bg-gray-50 text-sm mt-4">
                    <p class="font-medium">{{ __('certifications.selected_certification') }}:</p>
                    <p>{{ $selectedCertification->name }}</p>
                </div>

                {{-- Price Option Selection --}}
                @if ($selectedCertification->isFree())
                    {{-- Free certification message --}}
                    <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-sm font-medium text-green-800">{{ __('certifications.certification_is_free') }}</span>
                        </div>
                    </div>
                @elseif ($actorType === 'federation')
                    {{-- Federation: no payment document generated --}}
                    <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-sm font-medium text-blue-800">{{ __('certifications.federation_no_payment_document') }}</span>
                        </div>
                    </div>
                @else
                    {{-- Entity: show price options --}}
                    <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <label class="block text-sm font-medium text-blue-900 mb-3">{{ __('certifications.select_price_option') }}</label>
                        <div class="space-y-2">
                            {{-- Digital Only Option --}}
                            <label class="flex items-center p-3 border rounded-lg cursor-pointer transition
                                {{ $selectedPriceOption === 'digital' ? 'border-blue-500 bg-white ring-2 ring-blue-500' : 'border-gray-200 bg-white hover:border-blue-300' }}">
                                <input type="radio" wire:model.live="selectedPriceOption" value="digital"
                                    class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                <div class="ml-3 flex-1">
                                    <span class="font-medium text-gray-900">{{ __('certifications.digital_only') }}</span>
                                    <p class="text-xs text-gray-500">{{ __('certifications.digital_only_description') }}</p>
                                </div>
                                <span class="text-green-600 font-semibold">{{ number_format($selectedCertification->getDigitalPrice(), 2) }}</span>
                            </label>

                            {{-- Digital + Physical Card Option (only if available) --}}
                            @if ($selectedCertification->hasCardOption())
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer transition
                                    {{ $selectedPriceOption === 'digital_plus_card' ? 'border-blue-500 bg-white ring-2 ring-blue-500' : 'border-gray-200 bg-white hover:border-blue-300' }}">
                                    <input type="radio" wire:model.live="selectedPriceOption" value="digital_plus_card"
                                        class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                    <div class="ml-3 flex-1">
                                        <span class="font-medium text-gray-900">{{ __('certifications.digital_plus_card') }}</span>
                                        <p class="text-xs text-gray-500">{{ __('certifications.digital_plus_card_description') }}</p>
                                    </div>
                                    <span class="text-green-600 font-semibold">{{ number_format($selectedCertification->getDigitalPlusCardPrice(), 2) }}</span>
                                </label>
                            @endif
                        </div>

                        {{-- Tax info --}}
                        @if ($selectedCertification->tax_percentage > 0)
                            <p class="text-xs text-gray-500 mt-2">
                                {{ __('certifications.tax_included', ['percentage' => number_format($selectedCertification->tax_percentage, 0)]) }}
                            </p>
                        @endif
                    </div>
                @endif
            @endif
        </div>

        <!-- Roles selection -->
        <div class="space-y-3">

            {{-- Student Selection Accordion --}}
            <div x-data="{ isOpen: true }" class="border rounded-lg overflow-hidden">
                {{-- Accordion Header --}}
                <button @click="isOpen = !isOpen" type="button"
                    class="flex justify-between items-center w-full p-4 bg-slate-200 text-slate-800 hover:bg-slate-100">
                    <div class="text-left">
                        <h3 class="font-medium">{{ __('certifications.students') }}</h3>
                        <span class="text-xs text-gray-500">
                            {{ $selectedStudents->count() }} {{ __('certifications.selected') }}
                        </span>
                    </div>
                    <x-icon name="heroicon-o-chevron-down"
                        class="w-5 h-5 text-gray-400 transform transition-transform duration-200"
                        x-bind:class="{ '-rotate-180': isOpen }" />
                </button>
                {{-- Accordion Content --}}
                <div x-show="isOpen" x-collapse class="p-4 border-t bg-slate-100">
                    {{-- National Certification Number input for each student --}}
                    @if($selectedStudents->isNotEmpty())
                        <div class="mt-6">
                            <h4 class="text-base font-semibold text-slate-600 mb-2">
                                {{ __('certifications.student_identification') }}
                            </h4>
                            <div class="divide-y divide-gray-200 border rounded-lg bg-white shadow-sm">
                                @foreach($selectedStudents as $student)
                                    <div class="flex flex-col md:flex-row md:items-center gap-4 px-4 py-4">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-3">
                                                <div class="flex-shrink-0">
                                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-700 font-bold text-lg">
                                                        {{ strtoupper(substr($student->full_name, 0, 1)) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">{{ $student->full_name }}</div>
                                                    @if($student->member_code)
                                                        <div class="text-xs text-gray-500">CMAS: {{ $student->member_code }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @if ($this->actorType === 'federation')
                                        <div class="flex-1 md:max-w-xs">
                                            <label for="student-national-{{ $student->id }}" class="block text-sm font-medium text-gray-700 mb-1">
                                                {{ __('certifications.national_certification_no') }}
                                            </label>
                                            <input
                                                id="student-national-{{ $student->id }}"
                                                type="text"
                                                class="form-input w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm @error('studentNationalNumbers.' . $student->id) border-red-500 @enderror"
                                                placeholder="{{ __('certifications.example_number') }}"
                                                wire:model.defer="studentNationalNumbers.{{ $student->id }}"
                                                autocomplete="off"
                                                required
                                                aria-describedby="student-national-{{ $student->id }}-help"
                                            />
                                            <div class="text-xs text-gray-400 mt-1" id="student-national-{{ $student->id }}-help">
                                                {{ __('certifications.enter_official_number') }}
                                            </div>
                                            @error('studentNationalNumbers.' . $student->id)
                                                <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    <livewire:certifications.wizard.student-selector-table :selected-school-id="$selectedSchoolId" :selected-federation-id="$selectedFederationId"
                        :selected-student-ids="$selectedStudentIds"
                        wire:key="student-selector-{{ $selectedSchoolId ?? 'x' }}-{{ is_array($selectedStudentIds) && count($selectedStudentIds) ? implode('-', $selectedStudentIds) : 'none' }}" />
                </div>
            </div>
        </div>

    </div>

    {{-- Legal disclaimer with confirmation checkbox --}}
    <div class="mt-6 border-2 border-amber-300 bg-amber-50 rounded-lg shadow-sm p-4">
        <div class="flex items-start">
            <div class="flex-shrink-0 mt-0.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-500" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div class="ml-3 flex-1">
                <h4 class="text-sm font-semibold text-amber-800">{{ __('certifications.important_certification_confirmation') }}</h4>
                <p class="mt-2 text-sm text-gray-700">
                    {{ __('certifications.confirmation_text') }}
                </p>
                <div class="mt-4">
                    <label class="flex items-start gap-3 cursor-pointer group">
                        <input type="checkbox"
                            wire:model.live="confirmationAccepted"
                            class="mt-0.5 h-5 w-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500 cursor-pointer" />
                        <span class="text-sm font-medium text-gray-800 group-hover:text-gray-900">
                            {{ __('certifications.confirmation_checkbox_label') }}
                        </span>
                    </label>
                    @error('confirmationAccepted')
                        <p class="mt-2 text-sm text-red-600">{{ __('certifications.confirmation_required') }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>


</div>
