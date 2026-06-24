<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <!-- Title -->
            <h1 class="page-first-title"> {{ ucwords($type) }} {{ __('license request') }} </h1>
        </div>


        <x-information-box
            title="Information"
            :body="__('To request a license, choose a license from the available selection. To activate the license, the order document generated must be paid. The license will only become active after the payment is confirmed.')"></x-information-box>
        <div>
            <form action="{{ route(Request::segment(1).'.license-attributed.store') }}" method="POST">
                @csrf
                <input type="hidden" name="license_type_name" value="entity">
                <input type="hidden" name="is_self_request" value="true">
                <input type="hidden" name="federation_id" value="{{ $federation->id }}">
                <input type="hidden" name="entity_id" value="{{ $entity->id }}">

                <div class="sm:flex sm:space-x-4">

                    <div class="sm:w-full">
                        <div class="card flex flex-col md:flex-row md:-mr-px">
                            <div class="grow">
                                <livewire:entity.entity-license-request-selector
                                    :federation="$federation"
                                    :type="$type">
                                </livewire:entity.entity-license-request-selector>
                                <div class="w-full mt-4">
                                    <label class="block text-sm font-medium mb-1"
                                           for="license_id"> {{ __('Notes') }}</label>
                                    <textarea class="form-textarea w-full" rows="2" name="notes"></textarea>
                                    <div class="text-xs mt-1">{{ __('Add some notes to the current request if needed') }}</div>
                                </div>
                            </div>
                        </div>

                        <x-forms.card-form-submit :backRoute="Request::segment(1).'.license-attributed.index'"
                                                  :buttonText="__('Save Request')"></x-forms.card-form-submit>

                    </div>
                </div>
            </form>
        </div>
    </div>

</x-layout>
