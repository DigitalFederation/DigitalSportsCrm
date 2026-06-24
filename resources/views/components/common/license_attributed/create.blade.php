<div>
    <form action="{{ route(Request::segment(1) . '.license-attributed.store') }}"
          method="POST"
          onsubmit="document.getElementById('license_id').disabled = false">
        @csrf


        <input type="hidden" name="committee" value="{{ $committee }}">
        <input type="hidden" name="license_type_name" value="{{ $licenseTypeName }}">
        <input type="hidden" name="requester_model_type" value="{{ $requesterModelType }}">

        <!-- Header information -->

        <!-- Panel left -->
        <div class="flex flex-col md:flex-row md:-mr-px gap-x-6">
            <!-- Panel body -->
            <livewire:get-individual-by-code-for-license
                :entities="$entities"
                :entity="$entity"
                :federations="$federations"
                :federation="$federation"
                :committee="$committee"
                :licenseTypeName="$licenseTypeName"
                :professionalRole="$professionalRole" />
          
        </div>

        <div class="card mt-6">
            <div class="flex flex-col md:-mr-px gap-y-4">
                <!-- Extra info body  -->
                <div class="flex flex-col md:flex-row gap-x-4">
                    <div class="w-full md:w-1/4">
                        <label class="block text-sm font-medium mb-1" for="current_term_starts_at">
                            {{ __('Start date') }}</label>
                        <input type="date" name="current_term_starts_at" id="current_term_starts_at"
                               class="form-input w-full" value="{{ old('current_term_starts_at', date('Y-m-d')) }}">
                        <div class="text-xs mt-1">
                            {{ __('The start date for this license.') }}
                        </div>

                        @if ($errors->has('current_term_starts_at'))
                            <div class="text-xs mt-1 text-rose-500 h-2">
                                {{ $errors->first('current_term_starts_at') }}
                            </div>
                        @endif
                    </div>
                    <div class="w-full md:w-1/4">
                        <label class="block text-sm font-medium mb-1" for="current_term_ends_at">
                            {{ __('Expiration date') }}</label>
                        <input type="date" name="current_term_ends_at" id="current_term_ends_at"
                               class="form-input w-full" value="{{ old('current_term_ends_at') }}">
                        <div class="text-xs mt-1">
                            {{ __('Leave empty to auto-calculate.') }}
                        </div>

                        @if ($errors->has('current_term_ends_at'))
                            <div class="text-xs mt-1 text-rose-500 h-2">
                                {{ $errors->first('current_term_ends_at') }}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="w-full">
                    <label class="block text-sm font-medium mb-1" for="license_id"> {{ __('Notes') }}</label>
                    <textarea class="form-input w-full" rows="2" name="notes"></textarea>
                    <div class="text-xs mt-1">Add some notes to the current request if needed</div>
                </div>
            </div>

            <div class="mt-6 md:-mr-px">
                <!-- Panel footer -->
                @include('components.forms.card-form-submit', [
                    'backRoute' => Request::segment(1) . '.license-attributed.index',
                    'buttonText' => __('Save request'),
                ])
            </div>
        </div>

    </form>
</div>
