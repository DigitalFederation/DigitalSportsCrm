@section('title', request()->get('type') === 'members' ? __('licenses.Purchase International Licenses for Members') : __('licenses.Purchase International Entity License'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- International Header -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 flex items-center">
            <div>
                <h2 class="text-lg font-semibold text-blue-900">{{ __('International Licenses') }}</h2>
                <p class="text-sm text-blue-700">{{ __('Official international licenses recognized worldwide') }}</p>
            </div>
        </div>

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                @if(request()->get('type') === 'members')
                    <h1 class="page-first-title">{{ __('licenses.Purchase International Licenses for Members') }}</h1>
                    <p class="text-slate-600">{{ __('licenses.Select members and purchase international licenses on their behalf') }}</p>
                @else
                    <h1 class="page-first-title">{{ __('licenses.Purchase International Entity License') }}</h1>
                    <p class="text-slate-600">{{ __('licenses.Purchase an international license for your organization') }}</p>
                @endif
            </div>
            
            <!-- Quick Switch Link -->
            <div class="flex items-center">
                @php
                    $switchParams = [];
                    $filter = request()->query('filter', []);
                    if (isset($filter['committee'])) {
                        $switchParams['filter']['committee'] = $filter['committee'];
                    }
                @endphp
                @if(request()->get('type') === 'members')
                    @php
                        $switchParams['type'] = 'entity';
                    @endphp
                    <a href="{{ route(Request::segment(1).'.international-license-purchase.index', $switchParams) }}" 
                       class="text-sm text-blue-600 hover:text-blue-700 underline">
                        {{ __('licenses.Switch to International Entity License Purchase') }} →
                    </a>
                @else
                    @php
                        $switchParams['type'] = 'members';
                    @endphp
                    <a href="{{ route(Request::segment(1).'.international-license-purchase.index', $switchParams) }}" 
                       class="text-sm text-blue-600 hover:text-blue-700 underline">
                        {{ __('licenses.Switch to International Member License Purchase') }} →
                    </a>
                @endif
            </div>
        </div>

        @if($federation)
            <form action="{{ route(Request::segment(1).'.international-license-purchase.store') }}" method="POST">
                @csrf
                <input type="hidden" name="entity_id" value="{{ $entity->id }}">
                
                <livewire:entity.license-purchase-form 
                    :entity="$entity"
                    :federation="$federation"
                    :committee="$committee"
                    :type="request()->get('type', 'entity')"
                    :is-international="true" />
            </form>
        @else
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                <div class="flex">
                    <svg class="w-5 h-5 text-yellow-400 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <h3 class="text-sm font-medium text-yellow-800">{{ __('licenses.No Federation Association') }}</h3>
                        <p class="text-sm text-yellow-700 mt-1">
                            {{ __('licenses.Your entity is not associated with any federation. Please contact your federation administrator to establish this association before purchasing international licenses.') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-layout>