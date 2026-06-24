<!-- get-individual-and-instructor-for-certification.blade.php -->
<section id="individual-instructor-component">
    <div class="w-full sm:flex card gap-x-4 mb-4">
        <div class="mb-4 sm:w-1/3">

            @if (!empty($federations))
                <div class="mb-4">
                    <div>
                        <label for="federation" class="block text-sm font-medium mb-1">{{ __('Federation') }} <span
                                class="text-rose-500">*</span></label>
                        <select name="federation_id" id="federation" class="form-select w-full"
                            wire:model.live="selectedFederation" required>
                            <option hidden selected value="">{{ __('Choose a federation') }}</option>
                            @foreach ($federations as $federation)
                                <option value="{{ $federation->id }}" @if (old('federation_id') === $federation->id) selected @endif>
                                    {{ $federation->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @else
                <input type="hidden" name="federation_id" value="{{ $federationId }}">
            @endif

            @if (!empty($entities))
                <div class="mb-4">
                    <label for="entity" class="block text-sm font-medium mb-1">{{ __('School') }}</label>
                    <select name="entity_id" id="entity" class="form-select w-full" wire:model.live="selectedEntity">
                        <option selected value="">{{ __('Choose a School') }}</option>
                        @foreach ($entities as $entity)
                            <option value="{{ $entity->id }}" @if (old('entity_id') === $entity->id) selected @endif>
                                {{ $entity->name }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="entity_id" value="{{ $entityId }}">
            @endif

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1" for="course_instructor">
                    {{ __('Course Director Instructor') }}</label>

                <div class="flex">
                    <input type="text" wire:model.live="codeInstructor" id="course_instructor"
                        class="form-input rounded-r-none disabled:opacity-25 w-full"
                        @if ($federationApprove) disabled @endif />

                    @if ($selectedEntity)
                        <x-entity-instructor-selector input-id="course_instructor" :entity-id="$selectedEntity"
                            :wire-model="'codeInstructor'" />
                    @endif
                </div>

                <div class="text-xs mt-1 text-gray-500 ">{{ __('Please provide a valid Nº Filiado') }}</div>
                @if (!empty($selected_license_error_instructor))
                    <p class="text-sm text-rose-500">{{ $selected_license_error_instructor }}</p>
                @endif
                <button type="button" class="ml-0 mt-2 btn-info btn-sm block w-full disabled:opacity-25"
                    wire:click="searchInstructor" @if ($federationApprove) disabled @endif>
                    {{ __('Select') }} </button>
            </div>

            @if ($is_federation || $is_admin)
                <div class="mb-4">
                    <div>
                        <input type="checkbox" wire:model.live="federationApprove" name="approved_by_federation"
                            id="federationApprove" class="form-checkbox">
                        <label class="text-xs font-medium"
                            for="federationApprove">{{ __('Approved by National Technical Committee') }}</label>
                    </div>
                    <p class="text-xs mt-1 text-gray-500">
                        {{ __('Select this option to attribute the certification without a course director.') }}</p>
                </div>
            @endif

            @if ($is_admin)
                <input type="hidden" value="true" name="approve_without_slots" />
            @endif

        </div>

        <div class="mb-4 sm:w-full">

            <!-- If there is no instructor or federation show a warning message  -->
            @if (empty($instructor) && !$federationApprove && empty($selected_federation))
                <x-information-box :title="'Instructions'" :body="'Fill the necessary data on the left column to start associating Individuals to this certification request.'">

                </x-information-box>
            @endif

            <!-- After an Instructor and a Federation is choosen the user must select the Individual code and other options  -->
            @if ((!empty($instructor) || $federationApprove) && !empty($selectedFederation))
                <div class=" mb-4">

                    <div class="w-full md:flex md:space-x-4 mb-6">
                        <div class="w-full md:w-1/2">
                            <div>
                                <label class="block text-sm font-medium mb-1" for="individual">
                                    {{ __('main.Member Code') }}
                                    <span class="text-rose-500">*</span></label>
                                <div class="flex relative">
                                    <input type="text" wire:model.live="codeIndividual" id="student_individual"
                                        class="form-input w-full rounded-r-none">
                                    @if ($is_federation && !$selectedEntity)
                                        <x-federation-individual-selector input-id="student_individual"
                                            :wire-model="'codeIndividual'" />
                                    @else
                                        <x-entity.entity-individual-selector input-id="student_individual"
                                            :entity-id="$selectedEntity" :wire-model="'codeIndividual'" />
                                    @endif

                                    <button type="button" class="rounded-l-none btn-sm btn-primary"
                                        wire:click="searchIndividual"> {{ __('Add') }} </button>
                                </div>
                            </div>
                            <div class="text-xs mt-1">{{ __('Enter a Nº Filiado for a member of your School') }}</div>

                            @if (!empty($selected_license_error_individual))
                                <p class="text-sm text-rose-500">{{ $selected_license_error_individual }}</p>
                            @endif
                        </div>

                        <div class="w-full md:w-1/2 ml-0">
                            <div>
                                <label class="block text-sm font-medium mb-1" for="code_assistant_instructor">
                                    {{ __('Assistant Instructor Nº Filiado') }}</label>
                                <div class="flex">
                                    <input type="text" wire:model.live="codeAssistantInstructor"
                                        id="code_assistant_instructor"
                                        class="form-input disabled:opacity-25 w-full rounded-r-none">

                                    @if ($is_federation && !$selectedEntity)
                                        <x-federation-individual-selector input-id="code_assistant_instructor"
                                            :wire-model="'codeAssistantInstructor'" />
                                    @else
                                        <x-entity.entity-individual-selector input-id="code_assistant_instructor"
                                            :entity-id="$selectedEntity" :wire-model="'codeAssistantInstructor'" />
                                    @endif

                                    <button type="button" class="rounded-l-none btn-sm btn-info disabled:opacity-25"
                                        wire:click="searchAssistantInstructor"> {{ __('Add') }} </button>
                                </div>
                            </div>
                            <div class="text-xs mt-1">
                                {{ __('Provide a valid Nº Filiado to find an assistant instructor') }}</div>

                            @if (!empty($selected_license_error_assistant_instructor))
                                <p class="text-sm text-rose-500">{{ $selected_license_error_assistant_instructor }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="w-full flex space-x-4 ">
                        <div class="md:w-1/2">
                            <label class="block text-sm font-medium mb-1" for="certification_id">
                                {{ __('Certification') }}
                                <span class="text-rose-500">*</span></label>
                            <select name="certification_id" id="certification_id" class="form-input w-full" required>
                                <option hidden selected value="">Choose a certification</option>
                                @foreach ($certifications as $certification)
                                    <option value="{{ $certification->id }}">{{ $certification->name }}</option>
                                @endforeach
                            </select>
                            <div class="text-xs mt-1">
                                {{ __('Choose the certification to be attributed for this request') }}</div>
                        </div>

                    </div>
                </div>
            @endif

            <!-- A Summary of the Choosen Individuals must be showed  -->
            @if (!empty($individual))
                <div class="card mb-4">

                    <p class="text-slate-900 font-bold mb-4 border-b border-gray-400">{{ __('Selected Individual') }}
                    </p>
                    @foreach ($individual as $index => $ind)
                        <div class="md:flex items-center mt-4 justify-between">
                            <div class="md:flex items-center">

                                <div class="flex justify-between items-center gap-x-4">

                                    <div class="flex gap-x-1">
                                        <button type="button"
                                            wire:click="removeItem('INDIVIDUAL', {{ $index }})"
                                            class="btn-xs bg-red-500 hover:bg-red-600 text-white mr-2">
                                            <x-svg.trash class="w-4 h-4" />
                                        </button>

                                        <a target="_blank" href="../individual/{{ $ind['id'] }}"
                                            class="btn-xs btn-info" title="{{ __('Profile') }}">
                                            <x-svg.person-lines class="w-4 h-4" />
                                        </a>
                                    </div>


                                    <div class="flex flex-col md:flex-row gap-x-2">
                                        <div class="font-bold text-sm">{{ __('Name') }}:</div>
                                        <div class="text-sm"> {{ $ind['name'] }} {{ $ind['surname'] }} </div>
                                    </div>


                                </div>

                            </div>

                            <div class="md:flex gap-x-2 items-center">
                                @if (
                                    (auth()->user()->group()->first()->code == 'FEDERATION' && !auth()->user()->federations()->first()->is_local) ||
                                        auth()->user()->group()->first()->code == 'ADMIN')
                                    <div class="font-bold text-sm">{{ __('National Certification nº') }}</div>
                                    <input type="text" placeholder="XXX/F00/ZZ/9999/888888"
                                        name="individual[national_code][]" class="form-input w-full">
                                @endif
                            </div>
                        </div>

                        <input name="individual[id][]" value="{{ $ind['id'] }}" type="hidden">
                        <input name="individual[name][]" value="{{ $ind['name'] . ' ' . $ind['surname'] }}"
                            type="hidden">

                        @if (!empty($ind['certifications_attributed']) && count($ind['certifications_attributed']) > 1000)
                            <table class="w-full text-sm text-left text-gray-500 mt-4">
                                <thead class="text-xs uppercase text-slate-500 bg-slate-200 rounded-sm">
                                    <tr>
                                        <th class="p-2">
                                            <div class="font-semibold text-left">{{ __('Certifications') }}</div>
                                        </th>
                                        <th class="p-2">
                                            <div class="font-semibold text-right">{{ __('Expiration Date') }}</div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($ind['certifications_attributed'] as $certificationAttributed)
                                        <tr class="border-b border-gray-200">
                                            <td class="py-1">{{ $certificationAttributed['certification']['name'] }}
                                            </td>
                                            <td class="py-1 text-right">
                                                @if ($certificationAttributed['current_term_ends_at'])
                                                    {{ date('d/m/Y', strtotime($certificationAttributed['current_term_ends_at'])) }}
                                                @else
                                                    <div
                                                        class="inline-flex items-center text-xs font-medium text-slate-100 bg-slate-700 rounded-full text-center px-2 py-0.5">
                                                        <svg class="w-3 h-3 shrink-0 fill-current text-amber-500 mr-1"
                                                            viewBox="0 0 12 12">
                                                            <path
                                                                d="M11.953 4.29a.5.5 0 00-.454-.292H6.14L6.984.62A.5.5 0 006.12.173l-6 7a.5.5 0 00.379.825h5.359l-.844 3.38a.5.5 0 00.864.445l6-7a.5.5 0 00.075-.534z">
                                                            </path>
                                                        </svg>
                                                        {{ __('No expire date') }}
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    @endforeach

                </div>
            @endif

            <!-- The profile for the Course Director  must be showed  -->
            @if (!empty($instructor))
                <div class="card">
                    <div class="mt-2">
                        @if (!empty($instructor))
                            <p class="text-slate-900 font-bold mb-4"> {{ __('Course Director Instructor') }} </p>
                            <input name="instructor_id" value="{{ $instructor->id }}" type="hidden">

                            <table class="w-full text-sm text-left text-gray-500 mt-4">
                                <thead class="text-xs uppercase text-slate-500 bg-slate-200 rounded-sm">
                                    <tr>
                                        <th class="p-2">
                                            <div class="font-semibold text-left">{{ __('Name') }}</div>
                                        </th>
                                        <th class="p-2">
                                            <div class="font-semibold text-center">{{ __('main.Member Code') }}</div>
                                        </th>
                                        <th class="p-2">
                                            <div class="font-semibold text-center">{{ __('Profile') }}</div>
                                        </th>
                                        <th class="p-2"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-b border-gray-300">
                                        <td class="py-1">
                                            <strong>{{ $instructor['name'] }} {{ $instructor['surname'] }}</strong>
                                        </td>
                                        <td class="py-1 text-center">{{ $instructor['member_code'] }}</td>
                                        <td class="py-1 text-center">
                                            <a href="{{ route(strtolower(auth()->user()->group()->first()->name) . '.individual.show', $instructor['id']) }}"
                                                target="_blank"
                                                class="text-xs py-0 btn-outline btn-sm hover:btn-outline-hover">
                                                {{ __('Detail') }}
                                            </a>
                                        </td>
                                        <td class="py-1 text-right">
                                            <button type="button" wire:click="removeItem('INSTRUCTOR')"
                                                class="btn-xs bg-red-500 hover:bg-red-600 text-white mr-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                    class="w-4 h-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            @endif


            <!-- If there are any assistants the profile must be showed  -->
            @if (!empty($assistantInstructor))
                <div class="card mt-4">

                    @if (!empty($assistantInstructor))

                        <p class="text-slate-900 font-bold mb-4"> {{ __('Assistant Instructor(s)') }} </p>
                        @foreach ($assistantInstructor as $assistant)
                            <div class="flex items-center mt-4">
                                <button type="button" wire:click="removeItem('ASSISTANT')"
                                    class="btn-xs bg-red-500 hover:bg-red-600 text-white mr-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>
                                <div><strong>{{ __('Name') }}
                                        : </strong> {{ $assistant['name'] }} {{ $assistant['surname'] }}
                                </div>
                                <div><small>( {{ $assistant['member_code'] }})</small></div>
                            </div>

                            <input name="assistant[]" value="{{ $assistant['id'] }}" type="hidden">
                        @endforeach
                    @endif

                </div>
            @endif


        </div>
    </div>
</section>
