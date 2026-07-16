<x-layout>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-6">
        <div class="card px-4 py-6 md:flex md:items-center md:justify-between">
            <div class="flex-1 min-w-0 flex items-center">
                <!-- Country Flag -->
                <div class="flex-shrink-0 h-16 w-16 mr-4">
                    <img class="h-16 w-16 rounded-full object-cover"
                         src="{{ $federation->country?->iso ? asset('img/flags/'.strtolower($federation->country->iso).'.svg') : asset('img/flags/default.svg') }}"
                         alt="{{ $federation->country?->name ?? 'Unknown Country' }} Flag">
                </div>

                <!-- Federation Logo -->
                <div class="flex-shrink-0 h-16 w-16 mr-4">
                    @if($federation->getFirstMediaUrl('logo'))
                        <img class="h-16 w-16 rounded-full object-cover"
                             src="{{ $federation->getFirstMediaUrl('logo') }}"
                             alt="{{ $federation->name ?? 'Federation' }} Logo">
                    @else
                        <div class="h-16 w-16 rounded-full bg-gray-200 flex items-center justify-center">
                                <span class="text-gray-500 font-semibold text-xl">
                                    {{ strtoupper(substr($federation->name ?? '??', 0, 2)) }}
                                </span>
                        </div>
                    @endif
                </div>

                <!-- Federation Name and Code -->
                <div class="min-w-0 flex-1">
                    <h1 class="text-xl font-bold leading-7 text-gray-900 truncate" title="{{ $federation->name ?? 'Unknown Federation' }}">
                        {{ $federation->name ?? 'Unknown Federation' }}
                    </h1>
                    <p class="text-sm font-medium text-gray-500 truncate">
                        {{ $federation->member_code ?? 'N/A' }} • {{ $federation->country?->name ?? 'Unknown Country' }}
                    </p>
                </div>
            </div>
            <div class="mt-4 flex items-center md:mt-0 md:ml-4">

        
                <a href="{{ route('admin.federation.committees', $federation->id) }}"
                   class="ml-3 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="mr-2 -ml-1 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4zm3 1h6v2H7V5zm6 4H7v2h6V9zm-6 4h6v2H7v-2z" clip-rule="evenodd" />
                    </svg>
                    {{ __('federation.manage_committees')}}
                </a>

                <a href="{{ route('admin.federation.licenses', $federation->id) }}"
                   class="ml-3 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="mr-2 -ml-1 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zM4 8h12v8H4V8z" clip-rule="evenodd" />
                    </svg>
                    {{ __('Gerir Licenças associadas')}}
                </a>
               

                <a href="{{ route('admin.federation.edit', $federation->id) }}"
                   class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    {{ __('Editar')}}
                </a>
            </div>
        </div>
    </div>


    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    Informação
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                    Detalhes
                </p>
            </div>
            <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                <dl class="sm:divide-y sm:divide-gray-200">
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Legal Name
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $federation->legal_name ?? 'N/A' }}
                        </dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Address
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $federation->address ?? 'N/A' }}<br>
                            {{ $federation->city ?? 'N/A' }}, {{ $federation->zip_code ?? 'N/A' }}
                        </dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Contact Information
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <p>Email: <a href="mailto:{{ $federation->email ?? 'N/A' }}" class="text-indigo-600 hover:text-indigo-900">{{ $federation->email ?? 'N/A' }}</a></p>
                            <p>Phone: {{ $federation->phone ?? 'N/A' }}</p>
                            <p>Website: <a href="{{ $federation->website ?? 'N/A' }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">{{ $federation->website ?? 'N/A' }}</a></p>
                        </dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            VAT Number
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $federation->vat_number ?? 'N/A' }}
                        </dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Board Members
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            @if(is_array($federation->board_members) && count($federation->board_members) > 0)
                                <ul class="border border-gray-200 rounded-md divide-y divide-gray-200">
                                    @foreach($federation->board_members as $position => $member)
                                        @if(!empty($member))
                                            <li class="pl-3 pr-4 py-3 flex items-center justify-between text-sm">
                                                <div class="w-0 flex-1 flex items-center">
                                                    <svg class="flex-shrink-0 h-5 w-5 text-gray-400"
                                                         xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                         fill="currentColor" aria-hidden="true">
                                                        <path fill-rule="evenodd"
                                                              d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                                              clip-rule="evenodd" />
                                                    </svg>
                                                    <div class="ml-2 flex-1 w-0 truncate">
                                                        <span class="font-bold">{{ $position ?? 'N/A' }}</span> - {{ is_array($member) ? implode(', ', $member) : $member }}
                                                    </div>
                                                </div>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-gray-500 italic">No board members information available.</p>
                            @endif
                        </dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Parent Federation
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $federation->parentFederation?->name ?? 'N/A' }}
                        </dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            {{ __('main.Zones') }}
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            @if($federation->zones->count() > 0)
                                <div class="flex flex-wrap gap-2">
                                    @foreach($federation->zones as $zone)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $zone->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-gray-500 italic">{{ __('main.no_zones_assigned') }}</span>
                            @endif
                        </dd>
                    </div>
                    <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">
                            Founded
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ $federation->founded_at?->format('Y') ?? 'N/A' }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="mt-8">
            <div x-data="{ activeTab: 'licenses' }" class="bg-white shadow sm:rounded-lg">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex" aria-label="Tabs">
                        <button @click="activeTab = 'licenses'"
                                :class="{'border-indigo-500 text-indigo-600': activeTab === 'licenses', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'licenses'}"
                                class="w-1/3 py-4 px-1 text-center border-b-2 font-medium text-sm">
                            Licenses
                        </button>
                        <button @click="activeTab = 'memberships'"
                                :class="{'border-indigo-500 text-indigo-600': activeTab === 'memberships', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'memberships'}"
                                class="w-1/3 py-4 px-1 text-center border-b-2 font-medium text-sm">
                            Memberships
                        </button>
                        <button @click="activeTab = 'documents'"
                                :class="{'border-indigo-500 text-indigo-600': activeTab === 'documents', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'documents'}"
                                class="w-1/3 py-4 px-1 text-center border-b-2 font-medium text-sm">
                            Documents
                        </button>
                    </nav>
                </div>
                <div class="p-4">
                    <div x-show="activeTab === 'licenses'">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Licenses</h3>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            @foreach(['DIVING', 'SCIENTIFIC', 'SPORT'] as $licenseType)
                                @php
                                    // Check if licensesCount and the specific type exist
                                    $countIndividual = $licensesCount[$licenseType]['individual'] ?? 0;
                                    $countEntity = $licensesCount[$licenseType]['entity'] ?? 0;
                                    $totalCount = $countIndividual + $countEntity;
                                @endphp
                                <div class="bg-white overflow-hidden shadow rounded-lg">
                                    <div class="px-4 py-5 sm:p-6">
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            {{ $licenseType }} Licenses
                                        </dt>
                                        <dd class="mt-1 text-3xl font-semibold text-gray-900">
                                            {{ $totalCount }}
                                        </dd>
                                    </div>
                                    <div class="bg-gray-50 px-4 py-4 sm:px-6">
                                        <div class="text-sm">
                                            <a href="{{ route('admin.license-attributed.index', ['filter[committee]' => strtolower($licenseType), 'filter[filter_federation]' => $federation->id]) }}"
                                               class="font-medium text-indigo-600 hover:text-indigo-500">
                                                View all<span class="sr-only"> {{ $licenseType }} licenses</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div x-show="activeTab === 'memberships'">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Memberships</h3>
                        <div class="space-y-4">
                            @forelse($federation->memberships ?? [] as $membership)
                                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                                    <div class="px-4 py-5 sm:p-6">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <h4 class="text-lg font-medium text-gray-900">
                                                    Membership #{{ $membership->id ?? 'N/A' }}
                                                </h4>
                                                <p class="text-sm text-gray-500 mt-1">
                                                    Status:
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                                        bg-{{ $membership->stateColor() ?? 'gray' }}-100 text-{{ $membership->stateColor() ?? 'gray' }}-800">
                                                        {{ ucfirst($membership->stateName() ?? 'Unknown') }}
                                                    </span>
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm text-gray-500">
                                                    <svg class="inline-block h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                    Started {{ $membership->current_term_starts_at?->format('M d, Y') }}
                                                </p>
                                                @if($membership->current_term_ends_at)
                                                    <p class="text-sm text-gray-500 mt-1">
                                                        <svg class="inline-block h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        Ends {{ $membership->current_term_ends_at?->format('M d, Y') }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="mt-4">
                                            <h5 class="text-sm font-medium text-gray-500 mb-2">Included Plans:</h5>
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                                @forelse($membership->plans ?? [] as $plan)
                                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                                        <svg class="flex-shrink-0 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        <div class="ml-3">
                                                            <p class="text-sm font-medium text-gray-900">{{ $plan->name ?? 'Unknown Plan' }}</p>
                                                            <p class="text-xs text-gray-500">Group {{ $plan->group ?? 'N/A' }}</p>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <p class="text-sm text-gray-500 col-span-full">No plans associated with this membership.</p>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-gray-50 px-4 py-3 sm:px-6">
                                        <div class="text-sm text-right">
                                            <a href="{{ route('admin.membership.show', $membership->id) }}"
                                               class="text-indigo-600 hover:text-indigo-500 font-medium">
                                                View Membership Details →
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-center text-gray-500">No memberships found.</p>
                            @endforelse
                        </div>
                    </div>
                    <div x-show="activeTab === 'documents'">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Documents</h3>
                        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                            <div class="min-w-full divide-y divide-gray-300">
                                <div class="bg-gray-50">
                                    <div class="grid grid-cols-12 gap-4 px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <div class="col-span-2">Reference</div>
                                        <div class="col-span-1">Type</div>
                                        <div class="col-span-4">Details</div>
                                        <div class="col-span-1">Status</div>
                                        <div class="col-span-2">Date</div>
                                        <div class="col-span-1">Amount</div>
                                        <div class="col-span-1 text-right">Actions</div>
                                    </div>
                                </div>
                                <div class="bg-white divide-y divide-gray-200">


                                    @forelse($invoices ?? [] as $document)
                                        <div class="hover:bg-gray-50 transition-colors duration-200">
                                            <div class="grid grid-cols-12 gap-4 px-6 py-4 items-center">
                                                <!-- Reference -->
                                                <div class="col-span-2">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        @if($document->invoice_number)
                                                            {{ $document->invoice_extended ?? 'N/A' }}
                                                        @else
                                                            {{ $document->number_extended ?? 'N/A' }}
                                                        @endif
                                                    </div>
                                                </div>

                                                <!-- Type -->
                                                <div class="col-span-1">
                                                    <div class="text-sm text-gray-900">
                                                        @if($document->invoice_number)
                                                            Invoice
                                                        @else
                                                            Order
                                                        @endif
                                                    </div>
                                                </div>

                                                <!-- Details -->
                                                <div class="col-span-4">
                                                    <div class="text-xs text-gray-500 mt-1 space-y-1">
                                                        @forelse($document->details ?? [] as $detail)
                                                            <div class="flex justify-between">
                                                                <span class="truncate max-w-[200px]">
                                                                    {{ ($detail->quantity ?? 0) }}x {{ $detail->description ?? 'N/A' }}
                                                                </span>
                                                                <span class="ml-2 whitespace-nowrap">
                                                                    {{ money($detail->unit_value ?? 0, $document->currency) }}
                                                                </span>
                                                            </div>
                                                        @empty
                                                            <p class="text-xs text-gray-400 italic">{{ __('No details') }}</p>
                                                        @endforelse
                                                    </div>
                                                </div>

                                                <!-- Status -->
                                                <div class="col-span-1">
                                                    <x-ux-badge-component
                                                        :status="str_replace('_', ' ', ucfirst($document->stateName() ?? 'Unknown'))"
                                                        :color="$document->stateColor() ?? 'gray'"
                                                        class="text-xs"
                                                    />
                                                </div>

                                                <!-- Date -->
                                                <div class="col-span-2">
                                                    <div class="text-sm text-gray-900">
                                                        {{ $document->created_at?->format('d M Y') ?? 'N/A' }}
                                                    </div>
                                                    <div class="text-xs text-gray-500">
                                                        {{ $document->created_at?->diffForHumans() ?? '' }}
                                                    </div>
                                                </div>

                                                <!-- Amount -->
                                                <div class="col-span-1">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ money($document->total_value ?? 0, $document->currency) }}
                                                    </div>
                                                </div>

                                                <!-- Actions -->
                                                <div class="col-span-1 flex justify-end space-x-2">
                                                    <a href="{{ route('admin.document.show', $document->id ?? '#') }}"
                                                       class="text-indigo-600 hover:text-indigo-900 {{ isset($document->id) ? '' : 'pointer-events-none opacity-50' }}"
                                                       title="View Details">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                        </svg>
                                                    </a>

                                                        <a href="{{ route('admin.document.download', $document->id ?? '#') }}"
                                                           class="text-gray-500 hover:text-gray-700 {{ isset($document->id) ? '' : 'pointer-events-none opacity-50' }}"
                                                           title="Download PDF">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                            </svg>
                                                        </a>

                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="px-6 py-4">
                                            <div class="text-center text-gray-500">
                                                No documents found
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        @if($invoices instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $invoices->hasPages())
                            <div class="mt-4">
                                {{ $invoices->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="my-8 bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    Federation Statistics
                </h3>
                <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                Total Individuals
                            </dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900">
                                {{ $federation->individuals?->count() ?? 0 }}
                            </dd>
                        </div>
                        <div class="bg-gray-50 px-4 py-4 sm:px-6">
                            <div class="text-sm">
                                <a href="{{ route('admin.individual.index', ['filter[filter_federation]' => $federation->id]) }}"
                                   class="font-medium text-indigo-600 hover:text-indigo-500">
                                    View all individuals
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                Total Entities
                            </dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900">
                                {{ $federation->entities?->count() ?? 0 }}
                            </dd>
                        </div>
                        <div class="bg-gray-50 px-4 py-4 sm:px-6">
                            <div class="text-sm">
                                <a href="{{ route('admin.entity.index', ['filter[filter_federation]' => $federation->id]) }}"
                                   class="font-medium text-indigo-600 hover:text-indigo-500">
                                    View all entities
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                Total Memberships
                            </dt>
                            <dd class="mt-1 text-3xl font-semibold text-gray-900">
                                {{ $federation->memberships?->count() ?? 0 }}
                            </dd>
                        </div>
                        <div class="bg-gray-50 px-4 py-4 sm:px-6">
                            <div class="text-sm">
                                <a href="{{ route('admin.membership.index', ['filter[filter_federation]' => $federation->id]) }}"
                                   class="font-medium text-indigo-600 hover:text-indigo-500">
                                    View all memberships
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>




    </div>

</x-layout>

