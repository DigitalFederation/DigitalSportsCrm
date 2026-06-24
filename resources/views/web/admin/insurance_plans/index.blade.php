<x-layout>
    <div class="previous-layout-classes">


        <div class="sm:flex sm:justify-between sm:items-center mb-4">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('main.insurance_plans') }}</h1>
            </div>
            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a class="btn btn-primary" href="{{ route('admin.insurance-plans.create') }}">
                    <span>{{ __('main.create_insurance_plan') }}</span>
                </a>
            </div>
        </div>

        <x-information-box title="{{ __('main.insurance_plans_info_title') }}"
            body="{{ __('main.insurance_plans_info_body') }}" />

        <div class="sm:flex sm:justify-center sm:items-center my-5 relative">
            <x-dynamic-table :pagination="$insurancePlans" paginationTitle="{{ __('main.insurance_plans') }}" :headers="[
                __('main.name'),
                __('main.target_audience'),
                __('main.type'),
                __('main.fee'),
                __('main.attachment'),
                '',
            ]">
                @foreach ($insurancePlans as $plan)
                    <tr>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            {{ $plan->name }}
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            {{ \App\Enums\InsurancePlansTargetAudienceEnum::from($plan->target_audience)->toString() }}
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            {{ $plan->type?->toString() ?? '-' }}
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            @if ($plan->target_audience === \App\Enums\InsurancePlansTargetAudienceEnum::INDIVIDUAL->value)
                                {{ number_format($plan->individual_fee, 2) }}
                            @else
                                {{ number_format($plan->entity_fee, 2) }}
                            @endif
                        </td>

                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            @if ($plan->getMedia('insurance_attachments')->count() > 0)
                                <div x-data="{ open: false }" class="inline-block">
                                    <button @click="open = !open" class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-paperclip"></i>
                                        {{ $plan->getMedia('insurance_attachments')->count() }}
                                    </button>
                                    <div x-show="open" @click.away="open = false"
                                        class="absolute z-10 mt-2 bg-white rounded-md shadow-lg">
                                        <div class="py-1">
                                            @foreach ($plan->getMedia('insurance_attachments') as $attachment)
                                                <a href="{{ route('admin.insurance-plans.download', ['id' => $plan->id, 'mediaId' => $attachment->id]) }}"
                                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    {{ $attachment->file_name }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span class="text-gray-400">{{ __('No attachments') }}</span>
                            @endif
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px items-end">
                            <div class="space-x-1 flex justify-end">

                                <x-dynamic-table-buttons type="show" :route="route(Request::segment(1) . '.insurance-plans.show', $plan->id)" />
                                <x-dynamic-table-buttons type="edit" :route="route(Request::segment(1) . '.insurance-plans.edit', $plan->id)" />
                                <x-dynamic-table-buttons type="delete" :route="route(Request::segment(1) . '.insurance-plans.destroy', $plan->id)" method="DELETE" />

                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-dynamic-table>
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $insurancePlans->links() }}
        </div>

    </div>
</x-layout>
