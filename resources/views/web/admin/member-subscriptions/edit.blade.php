<x-layout>
    <div class="previous-layout-classes">
        <div class="mb-8 flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Edit Member Subscription') }}</h1>
        </div>

        <x-information-box title="Information" body="This form allows you to edit an existing member subscription. Please update the fields as necessary.">
        </x-information-box>

        <div class="card">
            <form action="{{ route('federation.member-subscriptions.update', $subscription->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Member Type (read-only) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Member Type') }}</label>
                        <input type="text" value="{{ ucfirst($subscription->member_type) }}" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100" readonly>
                    </div>

                    <!-- Member (read-only) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Member') }}</label>
                        <input type="text" value="{{ $subscription->member->name }}" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100" readonly>
                    </div>

                    <!-- Membership Package -->
                    <div>
                        <label for="membership_package_id" class="block text-sm font-medium text-gray-700">{{ __('Membership Package') }}</label>
                        <select name="membership_package_id" id="membership_package_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            <!-- This should be populated with available membership packages -->
                            <option value="">{{ __('Select a package') }}</option>
                            @foreach($membershipPackages as $package)
                                <option value="{{ $package->id }}" {{ $subscription->membership_package_id == $package->id ? 'selected' : '' }}>
                                    {{ $package->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Start Date -->
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700">{{ __('Start Date') }}</label>
                        <input type="date" name="start_date" id="start_date" value="{{ $subscription->start_date->format('Y-m-d') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                    </div>

                    <!-- End Date -->
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700">{{ __('End Date') }}</label>
                        <input type="date" name="end_date" id="end_date" value="{{ $subscription->end_date->format('Y-m-d') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status_class" class="block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
                        <select name="status_class" id="status_class" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                            <option value="Domain\Memberships\States\ActiveMemberSubscriptionState" {{ $subscription->status_class == 'Domain\Memberships\States\ActiveMemberSubscriptionState' ? 'selected' : '' }}>{{ __('Active') }}</option>
                            <option value="Domain\Memberships\States\ExpiredMemberSubscriptionState" {{ $subscription->status_class == 'Domain\Memberships\States\ExpiredMemberSubscriptionState' ? 'selected' : '' }}>{{ __('Expired') }}</option>
                            <option value="Domain\Memberships\States\PendingMemberSubscriptionState" {{ $subscription->status_class == 'Domain\Memberships\States\PendingMemberSubscriptionState' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                        </select>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-8 flex justify-start gap-x-2">
                    <a href="{{ route('federation.member-subscriptions.show', $subscription->id) }}" class="btn btn-secondary">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        {{ __('Update Member Subscription') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layout>