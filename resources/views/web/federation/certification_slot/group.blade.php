<x-layout>
    @php
        $firstSlot = $slots->first();
        $federation = $firstSlot->federation;
        $federationLogoUrl = $federation?->getFirstMediaUrl('logo');
    @endphp

    <div class="previous-layout-classes">


        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ date('d/m/Y', strtotime($firstSlot->created_at)) }}
                    :: {{ $federation?->member_code . __(' Slot Order ') }} </h1>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-6 mt-16 ">

            <!-- Left column -->
            <div class="card col-span-full xl:col-span-6 flex flex-col flex-auto">

                <div class="-mt-16 md:mb-3 sm:mb-3 flex">

                    <div class="mb-4 h-24 w-24">
                        <img class="h-full w-full rounded-full border-4 border-white object-cover"
                             src="{{ asset('img/flags/'.strtolower($federation?->country->iso ?? '')).'.svg' }}"
                             alt="Country Flag">
                    </div>

                    <div class="ml-4 h-24 w-24">
                        @if($federationLogoUrl)
                            <img class="rounded-full border-4 border-white"
                                 src="{{ $federationLogoUrl }}" alt="Avatar">
                        @else
                            <img class="w-24 h-24 object-fit rounded-full border-4 border-white"
                                 src="{{ asset('img/user_placeholder.png') }}" alt="Avatar">
                        @endif
                    </div>

                </div>

                <!-- Card content -->
                @if(!empty($federation))
                    <div class="flex flex-col flex-auto mt-2">
                        <div class="mb-2">
                            <div class="text-secondary font-semibold">{{ __('certifications.federation_name')}}</div>
                            <p class="text-slate-500">{{ $federation?->name }}</p>
                        </div>
                        <div class="mb-2">
                            <div class="text-secondary font-semibold">{{ __('certifications.member_code')}}</div>
                            <p class="text-slate-500">{{ $federation?->member_code }}</p>
                        </div>
                        <div class="mb-2">
                            <div class="text-secondary font-semibold">{{ __('certifications.country')}}</div>
                            <p class="text-slate-500">{{ $federation?->country->name }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right column -->
            <div class="col-span-full xl:col-span-6 flex flex-col flex-auto">

                <div class="card gap-4 justify-around overflow-hidden">
                    <header class="pb-2 border-b border-slate-100">
                        <h2 class="font-semibold text-slate-800 text-left">{{ __('certifications.slot_order')}}</h2>
                        <div class="text-right">
                            <div class="absolute right-0 top-0 h-16 w-16">
                                <div
                                    class="absolute transform rotate-45 bg-{{ $firstSlot->stateColor() }} text-center text-white font-semibold py-1 right-[-40px] top-[30px] w-[170px]">
                                    {{ ucfirst($firstSlot->stateName()) }}
                                </div>
                            </div>
                        </div>
                    </header>


                    <div class="grid grid-cols-2 flex-auto mt-4">

                        <div class="mb-2">
                            <div class="text-secondary font-semibold">{{ __('certifications.total_price')}}</div>
                            <p class="text-slate-500">{{ money($totalPriceForOrder) }}</p>
                        </div>
                        <div class="mb-2">
                            <div class="text-secondary font-semibold">{{ __('certifications.status')}}</div>
                            <p class="text-slate-500">{{ ucwords($firstSlot->stateName()) }}</p>
                        </div>

                        @if(!empty($firstSlot->documentDetail))
                            <div class="mb-2">
                                <div class="text-secondary font-semibold">{{ __('certifications.payment_document')}}</div>
                                <p class="text-slate-500">
                                    <a href="{{ route('federation.document.show', $firstSlot->documentDetail->document_id) }}"
                                       target="_blank">

                                        <x-svg.box-arrow-up-right class="w-3 h-3 inline-block" />
                                        {{ __('certifications.view') }}   </a>

                                </p>
                            </div>
                        @endif


                        <div class="absolute bottom-0 right-0 w-24 h-24 -m-6">
                            @if($firstSlot->shipped_date)
                                <mat-icon role="img"
                                          class="mat-icon notranslate icon-size-24 opacity-25 text-green-500 dark:text-green-400 mat-icon-no-color"
                                          aria-hidden="true" data-mat-icon-type="svg" data-mat-icon-name="check-circle"
                                          data-mat-icon-namespace="heroicons_outline">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" fit="" height="100%" width="100%"
                                         preserveAspectRatio="xMidYMid meet" focusable="false">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </mat-icon>
                            @else
                                <mat-icon role="img"
                                          class="mat-icon notranslate icon-size-24 opacity-25 text-red-500 dark:text-red-400 mat-icon-no-color"
                                          aria-hidden="true" data-mat-icon-type="svg"
                                          data-mat-icon-name="exclamation-circle"
                                          data-mat-icon-namespace="heroicons_outline">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" fit="" height="100%" width="100%"
                                         preserveAspectRatio="xMidYMid meet" focusable="false">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </mat-icon>
                            @endif

                        </div>
                    </div>



                </div>
            </div>

        </div>

        <section class="mt-6">
            <x-dynamic-table :headers="[__('certifications.certification'), __('certifications.type'), __('certifications.qty'), __('certifications.unit_price'), __('certifications.total')]"
                             :items="$slots">
                @foreach($slots as $slot)
                    <tr>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 break-words w-px">
                            {{ $slot->certification->name }}
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-left">
                            {{ $slot->slotType->name }}
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-left">{{ $slot->quantity_original }}</td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            {{ money($slot->unit_price) }}
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-right">
                            {{ money($slot->total_price) }}
                        </td>
                    </tr>
                @endforeach
            </x-dynamic-table>
        </section>

    </div>
</x-layout>
