<x-layout>
    <div class="previous-layout-classes">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('District Details') }}: {{ $district->display_name }}</h1>
            </div>
            
            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a class="btn btn-info" href="{{ route('admin.districts.index') }}">
                    {{ __('Back to Districts') }}
                </a>
                <a class="btn btn-primary" href="{{ route('admin.districts.edit', $district) }}">
                    {{ __('Edit District') }}
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
                                <dd class="text-sm text-slate-900">{{ $district->name }}</dd>
                            </div>

                            @if($district->code)
                                <div>
                                    <dt class="text-sm font-medium text-slate-500 mb-1">{{ __('Code') }}</dt>
                                    <dd class="text-sm text-slate-900">
                                        <span class="inline-flex font-medium text-slate-800 rounded-full text-center px-2.5 py-1 bg-slate-100 text-slate-500 text-xs">
                                            {{ $district->code }}
                                        </span>
                                    </dd>
                                </div>
                            @endif

                            <div>
                                <dt class="text-sm font-medium text-slate-500 mb-1">{{ __('Country') }}</dt>
                                <dd class="text-sm text-slate-900">{{ $district->country->name }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-slate-500 mb-1">{{ __('Status') }}</dt>
                                <dd class="text-sm text-slate-900">
                                    @if($district->is_active)
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

                            @if($district->description)
                                <div class="md:col-span-2">
                                    <dt class="text-sm font-medium text-slate-500 mb-1">{{ __('Description') }}</dt>
                                    <dd class="text-sm text-slate-900">{{ $district->description }}</dd>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Zones -->
                @if($district->zones->isNotEmpty())
                    <div class="card overflow-hidden">
                        <h2 class="card-title">{{ __('Associated Zones') }} ({{ $district->zones->count() }})</h2>
                        <div class="mt-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach($district->zones as $zone)
                                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                        <div>
                                            <div class="font-medium text-slate-900">{{ $zone->name }}</div>
                                            @if($zone->code)
                                                <div class="text-sm text-slate-500">{{ $zone->code }}</div>
                                            @endif
                                        </div>
                                        <a href="{{ route('admin.zones.show', $zone) }}" 
                                           class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                            {{ __('View') }}
                                        </a>
                                    </div>
                                @endforeach
                            </div>
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
                            <span class="text-sm text-slate-500">{{ __('Zones') }}</span>
                            <span class="font-medium">{{ $district->zones->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-500">{{ __('Entities') }}</span>
                            <span class="font-medium">{{ $district->entities->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-500">{{ __('Federations') }}</span>
                            <span class="font-medium">{{ $district->federations->count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-slate-500">{{ __('Individuals') }}</span>
                            <span class="font-medium">{{ $district->individuals->count() }}</span>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card overflow-hidden">
                    <h2 class="card-title">{{ __('Information') }}</h2>
                    <div class="mt-4 space-y-3 text-sm">
                        <div>
                            <span class="text-slate-500">{{ __('Created') }}:</span>
                            <span class="font-medium">{{ $district->created_at->format('M j, Y') }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500">{{ __('Updated') }}:</span>
                            <span class="font-medium">{{ $district->updated_at->format('M j, Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>