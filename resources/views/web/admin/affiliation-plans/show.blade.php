<x-layout>
    <div class="previous-layout-classes">
        <div class="mb-8 flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Affiliation Plan Details') }}</h1>
            <div class="flex gap-2">
                <a href="{{ route('admin.affiliation-plans.edit', $plan) }}" class="btn btn-primary">
                    {{ __('Edit Plan') }}
                </a>
                <a href="{{ route('admin.affiliation-plans.index') }}" class="btn btn-info">
                    {{ __('Back to List') }}
                </a>
            </div>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <!-- Header Section -->
            <div class="bg-gray-50 px-6 py-4 border-b">
                <h2 class="text-xl font-semibold text-gray-900">{{ $plan->name }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ $plan->federation->name }}</p>
            </div>

            <!-- Content Section -->
            <div class="px-6 py-6 space-y-6">
                <!-- Basic Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Plan Type') }}</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $plan->type === 'individual' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ ucfirst($plan->type) }}
                            </span>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Duration') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $plan->duration_months }} {{ __('months') }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('VAT Rate') }}</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                {{ $plan->getVatRateLabel() }}
                            </span>
                        </dd>
                    </div>
                </div>

                <!-- Pricing Information -->
                <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-3">{{ __('Pricing') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if($plan->individual_fee)
                        <div>
                            <dt class="text-sm font-medium text-gray-600">{{ __('Individual Fee') }}</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900">
                                {{ money($plan->individual_fee) }}
                            </dd>
                        </div>
                        @endif

                        @if($plan->entity_fee)
                        <div>
                            <dt class="text-sm font-medium text-gray-600">{{ __('Entity Fee') }}</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900">
                                {{ money($plan->entity_fee) }}
                            </dd>
                        </div>
                        @endif
                    </div>

                    <!-- VAT Calculation Example -->
                    @if($plan->individual_fee || $plan->entity_fee)
                    <div class="mt-4 p-3 bg-white rounded border">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">{{ __('Price with VAT') }}</h4>
                        <div class="text-xs text-gray-600 space-y-1">
                            @if($plan->individual_fee)
                            <div class="flex justify-between">
                                <span>{{ __('Individual Fee (inc. VAT)') }}:</span>
                                <span class="font-medium">
                                    {{ money($plan->individual_fee * (1 + $plan->getVatRatePercentage() / 100)) }}
                                </span>
                            </div>
                            @endif
                            @if($plan->entity_fee)
                            <div class="flex justify-between">
                                <span>{{ __('Entity Fee (inc. VAT)') }}:</span>
                                <span class="font-medium">
                                    {{ money($plan->entity_fee * (1 + $plan->getVatRatePercentage() / 100)) }}
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Validity Period -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Start Date') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $plan->start_date ? $plan->start_date->format('d/m/Y') : __('Immediate availability') }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('End Date') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $plan->end_date ? $plan->end_date->format('d/m/Y') : __('No expiration') }}
                        </dd>
                    </div>
                </div>

                <!-- Status -->
                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('Status') }}</dt>
                    <dd class="mt-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                            {{ $plan->isActive() ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $plan->isActive() ? __('Active') : __('Inactive') }}
                        </span>
                    </dd>
                </div>

                <!-- Description -->
                @if($plan->description)
                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('Description') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ $plan->description }}</dd>
                </div>
                @endif

                <!-- Attachments -->
                @if($plan->getMedia('affiliation_attachments')->isNotEmpty())
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-3">{{ __('Attachments') }}</h3>
                    <div class="space-y-2">
                        @foreach($plan->getMedia('affiliation_attachments') as $media)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $media->file_name }}</p>
                                    <p class="text-xs text-gray-500">{{ number_format($media->size / 1024, 1) }} KB</p>
                                </div>
                            </div>
                            <a href="{{ route('admin.affiliation-plans.download', [$plan->id, $media->id]) }}" 
                               class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                {{ __('Download') }}
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-layout>