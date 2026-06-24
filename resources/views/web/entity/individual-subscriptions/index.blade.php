<x-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <div class="mb-8 flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Member Subscriptions') }}</h1>
        </div>

        <x-information-box
                title="{{ __('Information') }}"
                body="{{ __('This screen displays all subscriptions for your entity members. You can filter and view subscription details from here.') }}">
        </x-information-box>

        <div class="sm:flex flex-row gap-4">
            <!-- FILTER RESULTS -->
            <x-filter-form :post="route('entity.individual-subscriptions.index')">
                <x-forms.filter-input-select
                        label="{{ __('Status') }}"
                        name="filter[status_class]"
                        :options="[
                        '' => __('All'),
                        'Domain\Memberships\States\ActiveMemberSubscriptionState' => __('Active'),
                        'Domain\Memberships\States\ExpiredMemberSubscriptionState' => __('Expired'),
                        'Domain\Memberships\States\PendingMemberSubscriptionState' => __('Pending'),
                        'Domain\Memberships\States\PendingPaymentMemberSubscriptionState' => __('Pending Payment')
                    ]"
                />
                <x-forms.filter-input-text label="{{ __('Member Name') }}" name="filter[member.name]"/>
                <x-forms.filter-input-text label="{{ __('Package Name') }}" name="filter[membershipPackage.name]"/>
            </x-filter-form>
        </div>

        <x-dynamic-table
                :pagination="$subscriptions"
                paginationTitle="{{ __('Member Subscriptions') }}"
                :headers="[__('Member'), __('Package'), __('Start Date'), __('End Date'), __('Status')]">
            @foreach($subscriptions as $subscription)
                <tr>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        {{ $subscription->member?->full_name }}
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        {{ $subscription->membershipPackage->name }}
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        {{ $subscription->start_date->format('Y-m-d') }}
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        {{ $subscription->end_date->format('Y-m-d') }}
                    </td>
                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-right">
                        <x-tables.badge
                                :status="$subscription->state->name()"
                                :color="$subscription->state->color()"
                        />
                    </td>
                </tr>
            @endforeach
        </x-dynamic-table>

    </div>
</x-layout>