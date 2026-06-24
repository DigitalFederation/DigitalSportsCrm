<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('Entity Detail') }}</h1>
            </div>


        </div>

        <div class="grid grid-cols-12 gap-6 mt-16">

            <!-- Left column -->
            <div class="col-span-full xl:col-span-6 flex flex-col flex-auto">
                <div class="card">

                    <div class="-mt-16 mb-6 sm:mb-3">
                        <div class="inline-flex -ml-1 -mt-1 mb-4 sm:mb-0">
                            @if($entity->getFirstMediaUrl('profile', 'thumb'))
                                <img class="rounded-full border-4 border-white"
                                    src="{{ $entity->getFirstMediaUrl('profile', 'thumb') }}" alt="Avatar" width="128" height="128">
                            @else
                                <img class="w-24 h-24 object-fit rounded-full border-4 border-white"
                                    src="{{ asset('img/user_placeholder.png') }}" alt="Avatar">
                            @endif
                        </div>
                    </div>

                    <!-- Card content -->
                    <div class="flex flex-col flex-auto mt-6">
                        <div class="mb-2">
                            <div class="text-secondary font-semibold">{{ __('Entity Name')}}</div>
                            <p class="text-slate-500">{{ $entity->name }}</p>
                        </div>
                        <div class="mb-2">
                            <div class="text-secondary font-semibold">{{ __('main.Member Code')}}</div>
                            <p class="text-slate-500">{{ $entity->member_code }}</p>
                        </div>
                        <div class="mb-2">
                            <div class="text-secondary font-semibold">{{ __('Country')}}</div>
                            <p class="text-slate-500">{{ $entity->country->name }}</p>
                        </div>
                        <div class="mb-2">
                            <div class="text-secondary font-semibold">{{ __('Tax Identification Number')}}</div>
                            <p class="text-slate-500">
                                {{ $entity->vat_number }}
                            </p>
                        </div>
                        <div class="mb-2">
                            <div class="text-secondary font-semibold">{{ __('HQ Address, City, Postal Code')}}</div>
                            <p class="text-slate-500">
                                {{ $entity->address }} {{ $entity->zip_code }}
                            </p>
                        </div>
                        <div class="mb-2">
                            <div class="text-secondary font-semibold">{{ __('Website')}}</div>
                            <p class="text-slate-500">
                                @if($entity->website)
                                    <a href="{{ $entity->website }}" target="_blank"> Open url </a>
                                @else
                                    ---
                                @endif
                            </p>
                        </div>
                        <div class="mb-2">
                            <div class="text-secondary font-semibold">{{ __('Contact E-mail')}}</div>
                            <p class="text-slate-500"> {{ $entity->email ?: "---" }} </p>
                        </div>
                        <div class="mb-2">
                            <div class="text-secondary font-semibold">{{ __('Phone Number')}}</div>
                            <p class="text-slate-500">{{ $entity->phone ?: "---" }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 border-t divide-x -m-6 mt-4 bg-gray-50 dark:bg-transparent">
                        <div class="flex flex-col items-center justify-center p-6 sm:p-8">
                            <div class="text-5xl font-semibold leading-none tracking-tighter">
                                {{ $entity->individuals->count() }}
                            </div>
                            <div class="mt-1 text-center text-secondary">{{ __('Individuals') }}</div>
                            <a href="{{ route('admin.individual.index', ['filter[filter_entity]' => $entity->id]) }}"
                            class="btn btn-outline mt-2 text-center text-secondary">{{ __('View All') }}</a>
                        </div>
                    </div>

                </div>


            </div>

            <!-- Right column -->
            <div class="col-span-full xl:col-span-6 flex flex-col flex-auto">

                <div class="card sm:flex sm:flex-wrap gap-4 justify-around">

                    <x-utility.number-block
                        title="{{ __('Diving Certifications') }}"
                        itemFirstCount="{{$certificationsCount['DIVING']}}"
                        itemFirstTitle="View All"
                        :itemFirstRoute="route('admin.certification-attributed.index', ['filter[committee]' => 'diving', 'filter[filter_entity]' => $entity->id])"
                    >
                    </x-utility.number-block>

                    <x-utility.number-block
                        title="{{ __('Scientific Certifications') }}"
                        itemFirstCount="{{$certificationsCount['SCIENTIFIC']}}"
                        itemFirstTitle="View All"
                        :itemFirstRoute="route('admin.certification-attributed.index', ['filter[committee]' => 'scientific', 'filter[filter_entity]' => $entity->id])"
                    >
                    </x-utility.number-block>
                </div>

                <div class="card sm:flex sm:flex-wrap gap-4 justify-around mt-6">

                    <x-utility.number-block
                        title="{{ __('Diving Licenses') }}"
                        itemFirstCount="{{$licensesCount['DIVING']}}"
                        itemFirstTitle="View All"
                        :itemFirstRoute="route('admin.license-attributed.index', ['filter[committee]' => 'diving', 'filter[filter_entity]' => $entity->id])"
                    >
                    </x-utility.number-block>

                    <x-utility.number-block
                        title="{{ __('Scientific Licenses') }}"
                        itemFirstCount="{{$licensesCount['SCIENTIFIC']}}"
                        itemFirstTitle="View All"
                        :itemFirstRoute="route('admin.license-attributed.index', ['filter[committee]' => 'scientific', 'filter[filter_entity]' => $entity->id])"
                    >
                    </x-utility.number-block>

                    <x-utility.number-block
                        title="{{ __('Sport Licenses') }}"
                        itemFirstCount="{{$licensesCount['SPORT']}}"
                        itemFirstTitle="View All"
                        :itemFirstRoute="route('admin.license-attributed.index', ['filter[committee]' => 'sport', 'filter[filter_entity]' => $entity->id])"
                    >
                    </x-utility.number-block>
                </div>

                <div class="mt-6 card flex flex-col">

                    <header class="pb-2 border-b border-slate-100 flex justify-between">
                        <h2 class="font-semibold text-slate-800">{{ __('Instructors')}}</h2>
                        <div class="text-secondary font-medium">
                            {{ count($entity->entityProfessionals)}} active
                        </div>
                    </header>

                    <div class="overflow-x-auto mt-4 w-full">

                        @if($entity->entityProfessionals->count() > 0)
                            <table class="w-full bg-transparent" role="table">

                                <thead role="rowgroup">
                                <tr role="row">
                                    <th role="columnheader" class="text-left" aria-sort="none">
                                        Name
                                    </th>
                                    <th role="columnheader" class="text-right" aria-sort="none">
                                        {{ __('main.Member Code') }}
                                    </th>
                                </tr>
                                </thead>

                                <tbody role="rowgroup">
                                @foreach($entity->entityProfessionals as $instructor)
                                    <tr role="row">
                                        <td role="cell">
                                    <span class="pr-6 font-medium text-sm text-secondary whitespace-nowrap">
                                    {{ $instructor->instructor?->name }}
                                    </span>
                                        </td>
                                        <td role="cell" class="text-right">
                                    <span class=" font-medium text-sm text-secondary whitespace-nowrap">
                                        {{ $instructor->instructor?->member_code }}
                                    </span>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>

                                <tfoot role="rowgroup">
                                <tr role="row" class="h-16 border-0">
                                    <td colspan="2" class="" role="cell">
                                        <a href="{{ route('admin.individual.index', ['filter[filter_instructors]' => true, 'filter[filter_entity]' => $entity->id]) }}"
                                           class="btn-outline">
                                            <span>{{ __('See all instructors') }}</span>
                                        </a>
                                    </td>
                                </tr>
                                </tfoot>

                            </table>
                        @else

                            <div class="flex flex-col items-center justify-center h-64">
                                <div class="text-secondary font-medium">{{ __('No instructors yet') }}</div>
                            </div>

                        @endif

                    </div>

                </div>
            </div>
        </div>

        <div class="card mt-6">
            <header class="pb-2 border-b border-slate-100 flex justify-between">
                <h2 class="font-semibold text-slate-800">{{ __('Federations(s)')}}</h2>
            </header>

            <div class="mt-4">
                @if($entity->federations->count() > 0)
                    <table class="w-full">
                        <thead>
                            <tr>
                                <th class="text-left">{{ __('Federation') }}</th>
                                <th class="text-left">{{ __('Type') }}</th>
                                <th class="text-left">{{ __('Status') }}</th>
                                <th class="text-left">{{ __('National Number') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($entity->federations as $federation)
                                <tr>
                                    <td>{{ $federation->name }}</td>
                                    <td>
                                        @if($federation->parent_id)
                                            {{ __('Local Federation') }}
                                        @else
                                            {{ __('Main Federation') }}
                                        @endif
                                    </td>
                                    <td>
                                        <x-ux-badge-component
                                            :status="$entity->getFederationStateNameAttribute($federation)"
                                            :color="$entity->getFederationStateColorAttribute($federation)"
                                        />
                                    </td>
                                    <td>{{ $federation->pivot->national_federation_number }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="text-center text-gray-500 py-4">
                        {{ __('No federation memberships found') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layout>
