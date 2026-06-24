<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <h1 class="page-first-title"> {{ __('Edit Membership Association') }} </h1>
        </div>

        <x-information-box title="Information" body="This screen allows you to assign specific membership plans to a local Organization. This is important because by specifying which memberships a local federation can access, you can tailor their capabilities and access within the system.">
        </x-information-box>

        {{-- Activity Log Section --}}
        @if(!$activityLogs->isEmpty())
        <div class="card h-32 overflow-y-scroll mb-4">
            <h2 class="text-lg font-semibold mb-3"> {{ __('Audit Log') }}</h2>
            <ol class="relative border-l border-gray-200 dark:border-gray-700">
                @foreach($activityLogs as $log)
                    <li class="mb-6 ml-4">
                        <div class="absolute w-3 h-3 bg-gray-200 rounded-full mt-1.5 -left-1.5 border border-white"></div>
                        <time class="mb-1 text-sm font-normal leading-none text-gray-400 ">{{ $log->created_at->format('Y-m-d H:i:s') }}</time>
                        <p class="text-sm font-semibold text-gray-500">{{ $log->description }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
        @endif

        <div class="card">
            <form action="{{ route('federation.local-membership-plan.update', $localFederation->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="sm:flex sm:space-x-4 items-start">

                    <!-- Local Federations Dropdown -->
                    <div class="mb-4 sm:w-1/3">
                        <label for="local_federation_id" class="block text-sm font-medium text-gray-700">
                            {{ __('Select Organization') }}
                        </label>
                        <span class="text-gray-900">{{ $localFederation->name }}</span>

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
                            :inputSelected="$localFederation->localMembershipPlan->pluck('membership_plan_id')->toArray()"
                            :multiple="true"/>

                    </div>


                </div>

                <!-- Submit Button -->
                <x-forms.card-form-submit :backRoute="'federation.local-membership-plan.index'" :button-text="'Update Membership(s)'"></x-forms.card-form-submit>
            </form>
        </div>

    </div>
</x-layout>
