<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <h1 class="page-first-title"> {{ __('Add Memberships to Local Organizations') }} </h1>
        </div>

        <x-information-box title="Information" body="This screen allows you to assign specific membership plans to a local Organization. This is important because by specifying which memberships a local federation can access, you can tailor their capabilities and access within the system.">
        </x-information-box>

        <div class="card">
            <form action="{{ route('federation.local-membership-plan.store') }}" method="POST">
                @csrf

                <div class="sm:flex sm:space-x-4 items-start">

                    <!-- Local Federations Dropdown -->
                    <div class="mb-4 sm:w-1/3">
                        <label for="local_federation_id" class="block text-sm font-medium text-gray-700">
                            {{ __('Select Organization') }}
                        </label>
                        <select id="local_federation_id" name="local_federation_id" class="form-select w-full">
                            @foreach($localFederations as $localFederation)
                                <option value="{{ $localFederation->id }}">{{ $localFederation->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Memberships Dropdown -->
                    <div class="mb-4 sm:w-1/3">
                        <label for="membership_id" class="block text-sm font-medium text-gray-700">
                            {{ __('Select Membership') }}
                        </label>

                        <livewire:input.select-multiple
                            wire.model.live="local_membership_plans"
                            :items="$membershipPlans->pluck('name', 'id')"
                            inputId="local_membership_plans"
                            inputName="membership_plan_id[]"
                            identifier="membership_plan_id"
                            :multiple="true"/>

                    </div>

                </div>
                <!-- Submit Button -->
                <x-forms.card-form-submit :backRoute="'federation.local-membership-plan.index'" :button-text="'Create Membership(s)'"></x-forms.card-form-submit>
            </form>
        </div>

    </div>
</x-layout>
