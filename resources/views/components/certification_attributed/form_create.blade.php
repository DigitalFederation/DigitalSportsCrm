<div class="previous-layout-classes">

    <!-- Page header -->
    <div class="mb-8 flex justify-between">
        <!-- Title -->
        <h1 class="page-first-title">{{ (!empty(Request::query()['filter']) && !empty(Request::query()['filter']['committee']) ? ucfirst(Request::query()['filter']['committee']) . __(' Certification')  : __('Assign Certification')) }}</h1>
    </div>

    <form
        action="{{ route(Request::segment(1).'.certification-attributed.store', !empty(Request::query()['filter']) && !empty(Request::query()['filter']['committee']) ? ['committee_code' => Request::query()['filter']['committee']] : null) }}"
        method="POST">
        @csrf

        @if(!empty(Request::query()['filter']) && !empty(Request::query()['filter']['committee']) && Request::query()['filter']['committee'] === 'sport')

            <livewire:get-individual-without-instructor-for-certification
                committee_code="sport"
                :federations="$federations"
                :is_federation="$isFederation"
                :is_admin="$isAdmin"
                :federationId="$federationId" />

        @else

            <livewire:get-individual-and-instructor-for-certification
                :committee_code="!empty(Request::query()['filter']) && !empty(Request::query()['filter']['committee']) ? Request::query()['filter']['committee'] : null"
                :federations="$federations"
                :certifications="$certifications ?? []"
                :is_federation="$isFederation"
                :is_admin="$isAdmin"
                :federationId="$federationId"
                :entityId="$entityId" />

        @endif


        <div class="w-full">
            <div class="card">
                <div class="grow">
                    <div class="sm:flex sm:items-start space-y-4 sm:space-y-0 sm:space-x-4 pt-4 justify-between ">

                        @if(auth()->user()->group()->first()->code === 'FEDERATION' || auth()->user()->group()->first()->code === 'ADMIN')
                            <div class="md:w-64">
                                <label class="block text-sm font-medium mb-1"
                                       for="current_term_starts_at"> {{ __('Issue Date') }}</label>
                                <input type="date" name="current_term_starts_at"
                                       id="current_term_starts_at" class="form-input w-full"
                                       value="{{ old('current_term_starts_at') }}">
                                <div class="text-xs mt-1">{{ __("What's the start date?") }}</div>

                                @if($errors->has('current_term_starts_at'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('current_term_starts_at') }}
                                    </div>
                                @endif
                            </div>

                            <div class="md:w-64">
                                <label class="block text-sm font-medium mb-1"
                                       for="current_term_ends_at"> {{ __('Expiration date') }}</label>
                                <input type="date" name="current_term_ends_at" id="current_term_ends_at"
                                       class="form-input w-full"
                                       value="{{ old('current_term_ends_at') }}" />
                                <div class="text-xs mt-1">When does the certification will expire?</div>

                                @if($errors->has('current_term_ends_at'))
                                    <div class="text-xs mt-1 text-rose-500 h-2">
                                        {{ $errors->first('current_term_ends_at') }}
                                    </div>
                                @endif
                            </div>
                        @endif

                        <div class="md:w-full">
                            <label class="block text-sm font-medium mb-1"
                                   for="notes"> {{ __('Notes') }}</label>
                            <textarea id="notes" class="form-textarea w-full" rows="2" name="notes"></textarea>
                            <div class="text-xs mt-1">Add some notes to the current request if
                                needed
                            </div>
                        </div>

                    </div>
                </div>

                <div class="flex information-box items-center w-full mt-4">

                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-4" width="24" height="24"
                         viewBox="0 0 24 24" stroke-width="1.5" stroke="#9e9e9e" fill="none" stroke-linecap="round"
                         stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <circle cx="12" cy="12" r="9" />
                        <line x1="12" y1="8" x2="12.01" y2="8" />
                        <polyline points="11 12 12 12 12 16 13 16" />
                    </svg>
                    <p class="text-sm">{{ __('By submitting this request for certification, you are confirming that the students have completed all the theoretical and practical training parts of the course programme and that the students have all the competencies described in the training standard.') }}</p>
                </div>

                <x-forms.card-form-submit backRoute="federation.certification-attributed.index"
                                          :buttonText="__('Save Request')"></x-forms.card-form-submit>

            </div>

        </div>
    </form>

</div>
