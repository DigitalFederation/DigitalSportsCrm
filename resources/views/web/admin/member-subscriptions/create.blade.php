<x-layout>
    <div class="previous-layout-classes">
        <div class="mb-8 flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Create Member Subscription') }}</h1>
        </div>

        <x-information-box title="Information" body="This form allows you to create a new member subscription. Please fill in all required fields.">
        </x-information-box>

        <div class="card">
            <form action="{{ route('admin.member-subscriptions.store') }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Member Type -->
                    <div>
                        <label for="member_type" class="block text-sm font-medium text-gray-700">{{ __('Member Type') }}</label>
                        <select name="member_type" id="member_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            <option value="">{{ __('Select member type') }}</option>
                            <option value="individual">{{ __('Individual') }}</option>
                            <option value="entity">{{ __('Entity') }}</option>
                        </select>
                    </div>

                    <!-- Individual Member -->
                    <div id="individual_member_div" style="display: none;">
                        <label for="individual_id" class="block text-sm font-medium text-gray-700">{{ __('Individual') }}</label>
                        <select name="individual_id" id="individual_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">{{ __('Select an individual') }}</option>
                            @foreach($individuals as $individual)
                                <option value="{{ $individual->id }}">{{ $individual->name }} {{ $individual->surname }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Entity Member -->
                    <div id="entity_member_div" style="display: none;">
                        <label for="entity_id" class="block text-sm font-medium text-gray-700">{{ __('Entity') }}</label>
                        <select name="entity_id" id="entity_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">{{ __('Select an entity') }}</option>
                            @foreach($entities as $entity)
                                <option value="{{ $entity->id }}">{{ $entity->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Membership Package -->
                    <div>
                        <label for="membership_package_id" class="block text-sm font-medium text-gray-700">{{ __('Membership Package') }}</label>
                        <select name="membership_package_id" id="membership_package_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            <option value="">{{ __('Select a package') }}</option>
                            @foreach($membershipPackages as $package)
                                <option value="{{ $package->id }}">{{ $package->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Start Date -->
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">{{ __('Start Date') }}</label>
                        <input type="date" name="start_date" id="start_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                    </div>

                    <!-- End Date -->
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">{{ __('End Date') }}</label>
                        <input type="date" name="end_date" id="end_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-8 flex justify-start gap-x-2">
                    <a href="{{ route('admin.member-subscriptions.index') }}" class="btn btn-info">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        {{ __('Create Member Subscription') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const memberTypeSelect = document.getElementById('member_type');
            const individualMemberDiv = document.getElementById('individual_member_div');
            const entityMemberDiv = document.getElementById('entity_member_div');

            memberTypeSelect.addEventListener('change', function() {
                if (this.value === 'individual') {
                    individualMemberDiv.style.display = 'block';
                    entityMemberDiv.style.display = 'none';
                } else if (this.value === 'entity') {
                    individualMemberDiv.style.display = 'none';
                    entityMemberDiv.style.display = 'block';
                } else {
                    individualMemberDiv.style.display = 'none';
                    entityMemberDiv.style.display = 'none';
                }
            });
        });
    </script>
</x-layout>