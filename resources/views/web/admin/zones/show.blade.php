<x-layout>
    <div class="previous-layout-classes">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('Zone Details') }}: {{ $zone->display_name }}</h1>
            </div>
            
            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a class="btn btn-info" href="{{ route('admin.zones.index') }}">
                    {{ __('Back to Zones') }}
                </a>
                <a class="btn btn-primary" href="{{ route('admin.zones.edit', $zone) }}">
                    {{ __('Edit Zone') }}
                </a>
            </div>
        </div>

        <div class="flex flex-col md:flex-row sm:items-start my-5 gap-4">
            <!-- Main Content -->
            <div class="md:w-2/3 flex flex-col gap-4">
                <!-- Basic Information -->
                <div class="card overflow-hidden">
                    <h2 class="card-title">{{ __('Basic Information') }}</h2>
                    <div class="mt-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-slate-500 mb-1">{{ __('Name') }}</dt>
                                <dd class="text-sm text-slate-900">{{ $zone->name }}</dd>
                            </div>

                            @if($zone->code)
                                <div>
                                    <dt class="text-sm font-medium text-slate-500 mb-1">{{ __('Code') }}</dt>
                                    <dd class="text-sm text-slate-900">
                                        <span class="inline-flex font-medium text-slate-800 rounded-full text-center px-2.5 py-1 bg-slate-100 text-slate-500 text-xs">
                                            {{ $zone->code }}
                                        </span>
                                    </dd>
                                </div>
                            @endif

                            <div>
                                <dt class="text-sm font-medium text-slate-500 mb-1">{{ __('Status') }}</dt>
                                <dd class="text-sm text-slate-900">
                                    @if($zone->is_active)
                                        <div class="inline-flex font-medium bg-emerald-100 text-emerald-600 rounded-full text-center px-2.5 py-1 text-xs">
                                            {{ __('Active') }}
                                        </div>
                                    @else
                                        <div class="inline-flex font-medium bg-slate-100 text-slate-500 rounded-full text-center px-2.5 py-1 text-xs">
                                            {{ __('Inactive') }}
                                        </div>
                                    @endif
                                </dd>
                            </div>

                            @if($zone->creator)
                                <div>
                                    <dt class="text-sm font-medium text-slate-500 mb-1">{{ __('Created By') }}</dt>
                                    <dd class="text-sm text-slate-900">{{ $zone->creator->name }}</dd>
                                </div>
                            @endif

                            @if($zone->description)
                                <div class="md:col-span-2">
                                    <dt class="text-sm font-medium text-slate-500 mb-1">{{ __('Description') }}</dt>
                                    <dd class="text-sm text-slate-900">{{ $zone->description }}</dd>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Districts -->
                @if($zone->districts->isNotEmpty())
                    <div class="card overflow-hidden">
                        <h2 class="card-title">{{ __('Districts in this Zone') }} ({{ $zone->districts->count() }})</h2>
                        <div class="mt-4">
                            @php
                                $districtsByCountry = $zone->districts->groupBy('country.name');
                            @endphp
                            
                            <div class="space-y-4">
                                @foreach($districtsByCountry as $countryName => $districts)
                                    <div class="border border-slate-200 rounded-lg p-4">
                                        <h4 class="font-medium text-slate-900 mb-3">{{ $countryName }} ({{ $districts->count() }} districts)</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            @foreach($districts as $district)
                                                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                                    <div>
                                                        <div class="font-medium text-slate-900">{{ $district->name }}</div>
                                                        @if($district->code)
                                                            <div class="text-sm text-slate-500">{{ $district->code }}</div>
                                                        @endif
                                                    </div>
                                                    <a href="{{ route('admin.districts.show', $district) }}" 
                                                       class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                                        {{ __('View') }}
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Associated Entities -->
                @if($zone->entities->isNotEmpty())
                    <div class="card overflow-hidden">
                        <h2 class="card-title">{{ __('Associated Entities') }} ({{ $zone->entities->count() }})</h2>
                        <div class="mt-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach($zone->entities->take(10) as $entity)
                                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                        <div>
                                            <div class="font-medium text-slate-900">{{ $entity->name }}</div>
                                            <div class="text-sm text-slate-500">{{ $entity->location }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if($zone->entities->count() > 10)
                                <div class="text-center mt-4">
                                    <span class="text-sm text-slate-500">{{ __('And :count more entities', ['count' => $zone->entities->count() - 10]) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Associated Federations -->
                @if($zone->federations->isNotEmpty())
                    <div class="card overflow-hidden">
                        <h2 class="card-title">{{ __('Associated Federations') }} ({{ $zone->federations->count() }})</h2>
                        <div class="mt-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach($zone->federations->take(10) as $federation)
                                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                        <div>
                                            <div class="font-medium text-slate-900">{{ $federation->name }}</div>
                                            <div class="text-sm text-slate-500">{{ $federation->location }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if($zone->federations->count() > 10)
                                <div class="text-center mt-4">
                                    <span class="text-sm text-slate-500">{{ __('And :count more federations', ['count' => $zone->federations->count() - 10]) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Associated Individuals -->
                @if($zone->individuals->isNotEmpty())
                    <div class="card overflow-hidden">
                        <h2 class="card-title">{{ __('Associated Individuals') }} ({{ $zone->individuals->count() }})</h2>
                        <div class="mt-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach($zone->individuals->take(10) as $individual)
                                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                        <div>
                                            <div class="font-medium text-slate-900">{{ $individual->full_name }}</div>
                                            <div class="text-sm text-slate-500">{{ $individual->location }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if($zone->individuals->count() > 10)
                                <div class="text-center mt-4">
                                    <span class="text-sm text-slate-500">{{ __('And :count more individuals', ['count' => $zone->individuals->count() - 10]) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="md:w-1/3 flex flex-col gap-4">
                <!-- Statistics -->
                <div class="card overflow-hidden">
                    <h2 class="card-title">{{ __('Statistics') }}</h2>
                    <div class="mt-4 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-500">{{ __('Districts') }}</span>
                            <span class="font-medium">{{ $zone->districts->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-500">{{ __('Countries') }}</span>
                            <span class="font-medium">{{ $zone->districts->pluck('country.name')->unique()->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-500">{{ __('Entities') }}</span>
                            <span class="font-medium">{{ $zone->entities->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-500">{{ __('Federations') }}</span>
                            <span class="font-medium">{{ $zone->federations->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-500">{{ __('Individuals') }}</span>
                            <span class="font-medium">{{ $zone->individuals->count() }}</span>
                        </div>
                    </div>
                </div>

                <!-- Information -->
                <div class="card overflow-hidden">
                    <h2 class="card-title">{{ __('Information') }}</h2>
                    <div class="mt-4 space-y-3 text-sm">
                        <div>
                            <span class="text-slate-500">{{ __('Created') }}:</span>
                            <span class="font-medium">{{ $zone->created_at->format('M j, Y') }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500">{{ __('Updated') }}:</span>
                            <span class="font-medium">{{ $zone->updated_at->format('M j, Y') }}</span>
                        </div>
                        @if($zone->creator)
                            <div>
                                <span class="text-slate-500">{{ __('Creator') }}:</span>
                                <span class="font-medium">{{ $zone->creator->name }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>