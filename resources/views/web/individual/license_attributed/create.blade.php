@section('title', __('License request'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-4 flex justify-between">
            <!-- Title -->
            <h1 class="page-first-title"> {{ __('Request License') }} </h1>
        </div>

        <x-information-box
            title="Licenses Information"
            body="{{__('Please note that you can only request sport licenses if your current federation does not offer licenses for the available sports. Otherwise, contact your National Federation.')}}" />

        <form action="{{ route('individual.license-attributed.store') }}" method="POST">
            @csrf
            <input type="hidden" name="license_type_name" value="individual">
            <!-- Important for the owner of the document -->
            <input type="hidden" name="is_self_request" value="true">
            <input type="hidden" name="individual[]" value="{{ $individual->id }}">
            <input type="hidden" name="requester_model_type" value="{{ $requester_model_type }}">

            <div class="sm:flex sm:space-x-4">

                <div class="sm:w-full">

                    <div class="card flex flex-col md:flex-row md:-mr-px">
                        <div class="grow">


                            <!-- livewire:individual-license-request-selector :federations="$federations"
                            :type="$type" -->


                            @if(!empty($licenses))
                                <div class="sm:w-1/3">
                                    <label for="license_id"
                                           class="block text-sm font-medium mb-1">{{ __('License') }}</label>
                                    <select name="license_id"
                                            id="license_id"
                                            class="form-select w-full"
                                            required>
                                        <option hidden selected>Select License...</option>
                                        @foreach($licenses as $license)
                                            <option value="{{ $license->id }}">{{ $license->name }}</option>
                                        @endforeach
                                    </select>

                                    @if($errors->has('license_id'))
                                        <div class="text-xs mt-1 text-rose-500 h-2">
                                            {{ $errors->first('license_id') }}
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <div class="w-full mt-4">
                                <label class="block text-sm font-medium mb-1"
                                       for="license_id"> {{ __('Notes') }}</label>
                                <textarea class="form-textarea w-full" rows="2" name="notes"></textarea>
                                <div class="text-xs mt-1">{{ __('Add some notes to the current request if needed') }}</div>
                            </div>
                        </div>
                    </div>

                    <x-forms.card-form-submit :backRoute="'individual.license-attributed.index'"
                                              :buttonText="__('Submit Request')"></x-forms.card-form-submit>

                </div>
            </div>
        </form>


    </div>

</x-layout>
