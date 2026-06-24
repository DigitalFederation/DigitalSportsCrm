<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('Membership detail') }}</h1>
            </div>


            <a href="{{ URL::previous() }}" class="btn btn-info flex justify-between">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                     stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                </svg>

                <span class="">{{ __('Back') }}</span>
            </a>
        </div>

        <div class="sm:flex sm:justify-center sm:items-start mb-5 md:space-x-4 space-y-4 md:space-y-0 relative">
            <div class="card md:w-2/3 overflow-hidden">
                <h2 class="card-title">{{ $membership->name ?: 'Membership '.$id }}</h2>

                <div class="mt-4">
                    @if(!empty($membership->activated_at))
                        <p class="text-sm text-gray-500"><strong>Activated
                                at:</strong> {{ date('d/m/Y', strtotime($membership->activated_at)) }}</p>
                    @endif
                    <p class="text-sm text-gray-500"><strong>Requested
                            at:</strong> {{ date('d/m/Y', strtotime($membership->created_at)) }}</p>
                    <p class="text-sm text-gray-500"><strong>Status:</strong> {{ ucwords($membership->stateName()) }}
                    </p>
                    <p class="text-sm text-gray-500 flex gap-1"><strong class="whitespace-nowrap">Membership
                            plans:</strong>
                        <span>
                            @foreach($membership->plans as $plan)
                                {{ $plan->name }}
                                <br>
                            @endforeach
                        </span>
                    </p>

                    @if($membership->stateName() == 'pending')
                        <div class="md:w-64">
                            <p class="text-sm text-gray-500 mb-4"><strong>Expires at:</strong> {{ date('d/m/Y', strtotime($membership->expires_at)) }}</p>
                            <a href="{{ route('admin.membership.activate', $membership->id) }}"
                               class="btn-primary mx-auto mt-4">
                                <span class="text-center mx-auto">{{ __('Activate Membership') }}</span>
                            </a>
                        </div>
                    @endif

                </div>

                <div class="absolute right-0 top-0 h-16 w-16">
                    <div
                        class="absolute transform rotate-45 bg-{{ $membership->stateColor() }} text-center text-white font-semibold py-1 right-[-40px] top-[30px] w-[170px]">
                        {{ ucfirst($membership->stateName()) }}
                    </div>
                </div>

                <div class="absolute bottom-0 right-0 w-24 h-24 -m-6">
                    @if($membership->stateName() == 'active')
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
                                  aria-hidden="true" data-mat-icon-type="svg" data-mat-icon-name="exclamation-circle"
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

            <div class="md:w-1/3 flex flex-col gap-y-5">
                <div class="card">
                    <h2 class="card-title">{{ $membership->federation->name }}</h2>

                    <div class="mt-4">
                        <p class="text-sm text-gray-500 md:flex justify-between items-center">
                            <span><strong>Profile:</strong> <a
                                    href="{{ route('admin.federation.show', $membership->federation_id)}}"> Open profile page</a></span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                 stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                            </svg>

                        </p>
                    </div>
                </div>

                @livewire('widget-activity-log', ['subject' => $membership, 'loadType' => 'poll'])

                @foreach($licenses->unique('committee_id') as $licenseCommittee)
                    <div class="card">
                        <h2 class="card-title">{{ __('Associated ') . ucfirst(strtolower($licenseCommittee->committee->code)) . __(' Licenses') }}</h2>
                        <table class="w-full">
                            <thead>
                            <tr>
                                @if($licenseCommittee->committee->code == 'SPORT')
                                    <th class="text-left">{{ __('Sport') }}</th>
                                @endif
                                <th class="text-left">{{ __('License name') }}</th>
                                <th class="text-left">{{ __('Type') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($licenses as $license)
                                @if($license->committee_id == $licenseCommittee->committee_id)
                                    <tr>
                                        @if($license->committee->code == 'SPORT')
                                            <td class="text-left">{{ $license->sport->name ?? null }}</td>
                                        @endif
                                        <td class="text-left">{{ $license->name }}</td>
                                        <td class="text-left">{{ ucfirst($license->type->name) }}</td>
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-layout>
