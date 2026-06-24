<x-layout>
    <div class="previous-layout-classes">
        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <!-- Title -->
            <h1 class="page-first-title">{{ __('diving.manage_certification_requirements') }}</h1>

            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.license-certification-requirements.edit', $license) }}" 
                   class="btn btn-primary">
                    {{ __('diving.edit_certification_requirements') }}
                </a>
                <a href="{{ route('admin.license.index') }}" 
                   class="btn btn-info">
                    {{ __('diving.back_to_license') }}
                </a>
            </div>
        </div>

        <!-- License Information -->
        <div class="bg-white shadow-lg rounded-sm mb-8">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-xl leading-snug text-slate-800 font-bold mb-1">{{ __('License') }}</h2>
                <p class="text-sm text-slate-600">{{ $license->name }}</p>
            </div>
        </div>

        <!-- Current Requirements -->
        <div class="bg-white shadow-lg rounded-sm">
            <div class="p-6">
                <h3 class="text-xl leading-snug text-slate-800 font-bold mb-4">
                    {{ __('diving.current_certification_requirements') }}
                </h3>

                @if($requirements->isNotEmpty())
                    <div class="space-y-3">
                        @foreach($requirements as $requirement)
                            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                <div>
                                    <span class="text-sm font-medium text-slate-900">
                                        {{ $availableComiteeDivingLevels[$requirement->certification_level] ?? $requirement->certification_level }}
                                    </span>
                                    <p class="text-xs text-slate-600 mt-1">
                                        {{ __('diving.requester_type') }}: {{ ucfirst($requirement->requester_type) }}
                                    </p>
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3 text-sm text-blue-700">
                                <p class="font-medium mb-1">{{ __('diving.note') }}</p>
                                <p>{{ __('diving.technical_director_requirements_note') }}</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="mx-auto h-12 w-12 text-slate-400">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 48 48" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M34 20v-8a2 2 0 00-2-2H8a2 2 0 00-2 2v28c0 1.1.9 2 2 2h8m18-18v18a2 2 0 01-2 2H20m14-20l4-4m0 0l4-4m-4 4l-4-4m4 4v16"/>
                            </svg>
                        </div>
                        <h3 class="mt-2 text-sm font-medium text-slate-900">
                            {{ __('diving.no_certification_requirements') }}
                        </h3>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ __('diving.no_certification_requirements_description') }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layout>