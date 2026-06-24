@section('title', __('diving.diving_professionals'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('diving.diving_professionals') }}</h1>
                <p class="text-sm text-slate-500">{{ __('diving.view_all_diving_professionals') }}</p>
            </div>
        </div>

        <!-- Filters -->
        <x-filter-form :post="route('admin.diving_professionals.index', ['tab' => $activeTab])">
            <x-forms.filter-input-text label="{{ __('diving.professional_name') }}" name="name" />
            <x-forms.filter-input-text label="{{ __('diving.member_number') }}" name="member_number" />
            <x-forms.filter-input-select label="{{ __('common.entity') }}" name="entity_id" :options="$entities" />
        </x-filter-form>

        <!-- Tabs -->
        <div class="mb-4">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <a href="{{ route('admin.diving_professionals.index', ['tab' => 'professionals'] + request()->except(['tab', 'professionals_page', 'directors_page'])) }}"
                       class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'professionals' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        {{ __('diving.diving_professionals') }}
                        <span class="ml-2 py-0.5 px-2.5 rounded-full text-xs font-medium {{ $activeTab === 'professionals' ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-900' }}">
                            {{ $professionals->total() }}
                        </span>
                    </a>

                    <a href="{{ route('admin.diving_professionals.index', ['tab' => 'directors'] + request()->except(['tab', 'professionals_page', 'directors_page'])) }}"
                       class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'directors' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        {{ __('diving.technical_directors') }}
                        <span class="ml-2 py-0.5 px-2.5 rounded-full text-xs font-medium {{ $activeTab === 'directors' ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-900' }}">
                            {{ $technicalDirectors->total() }}
                        </span>
                    </a>
                </nav>
            </div>
        </div>

        @if($activeTab === 'professionals')
            @if($professionals->count())
                <div class="sm:flex sm:justify-center sm:items-center mb-5">
                    <x-dynamic-table
                        :headers="[
                            __('diving.professional_name'),
                            __('common.birthdate'),
                            __('individuals.gender'),
                            __('diving.member_number'),
                            __('common.entity'),
                            __('diving.role'),
                            __('common.actions'),
                        ]">
                        @foreach($professionals as $professional)
                            @php
                                if ($professional->professionalRole->role === 'DIVINGPROFESSIONAL') {
                                    $roleLabel = stripos($professional->role_name, 'instructor') !== false
                                        ? __('diving.instructor')
                                        : __('diving.dive_leader');
                                } else {
                                    $roleLabel = $professional->role_name;
                                }

                                $genderLabel = match($professional->individual->gender) {
                                    'M', 'male' => __('individuals.male'),
                                    'F', 'female' => __('individuals.female'),
                                    default => $professional->individual->gender ?? '-'
                                };
                            @endphp
                            <tr>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            <x-secure-profile-image :individual="$professional->individual" size="thumb" class="h-8 w-8 rounded-full" />
                                        </div>
                                        <span class="font-medium text-slate-800">{{ $professional->individual->full_name }}</span>
                                    </div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    {{ $professional->individual->birthdate?->format('d/m/Y') ?? '-' }}
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    {{ $genderLabel }}
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    {{ $professional->individual->member_number ?? '-' }}
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    {{ $professional->entity->name }}
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <x-tables.badge :status="$roleLabel" color="blue" />
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 w-px">
                                    <div class="gap-x-2 flex justify-end">
                                        <x-dynamic-table-buttons type="show" :route="route('admin.entity.show', $professional->entity)" />
                                        <a href="{{ route('admin.individual.show', $professional->individual) }}" class="text-slate-500 hover:text-slate-800" title="{{ __('diving.view_profile') }}">
                                            @include('components.svg.person-badge', ['class' => 'h-5 w-5'])
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </x-dynamic-table>
                </div>

                <div class="mt-8">
                    {{ $professionals->withQueryString()->links() }}
                </div>
            @else
                <x-utility.no-data></x-utility.no-data>
            @endif
        @else
            @if($technicalDirectors->count())
                <div class="sm:flex sm:justify-center sm:items-center mb-5">
                    <x-dynamic-table
                        :headers="[
                            __('diving.professional_name'),
                            __('common.entity'),
                            __('diving.license'),
                            __('diving.role'),
                            __('common.actions'),
                        ]">
                        @foreach($technicalDirectors as $director)
                            <tr>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div>
                                        <div class="font-medium text-slate-800">{{ $director->individual->full_name }}</div>
                                        <div class="text-sm text-slate-500">{{ $director->individual->member_code }}</div>
                                    </div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    {{ $director->entity->name }}
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div>
                                        <div class="font-medium">{{ $director->licenseAttributed?->license?->name ?? '-' }}</div>
                                        <div class="text-sm text-slate-500">
                                            @if($director->licenseAttributed?->license?->type)
                                                {{ $director->licenseAttributed->license->type->name }}
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <x-tables.badge :status="__('diving.technical_director')" color="green" />
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 w-px">
                                    <div class="gap-x-2 flex justify-end">
                                        @if($director->licenseAttributed)
                                            <x-dynamic-table-buttons type="show" :route="route('admin.license-attributed.show', $director->licenseAttributed)" />
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </x-dynamic-table>
                </div>

                <div class="mt-8">
                    {{ $technicalDirectors->withQueryString()->links() }}
                </div>
            @else
                <x-utility.no-data></x-utility.no-data>
            @endif
        @endif
    </div>
</x-layout>
