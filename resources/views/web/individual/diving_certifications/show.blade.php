@section('title', __('diving.diving_certification_details'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8">
            <h1 class="page-first-title">{{ __('diving.diving_certification_details') }}</h1>
            <p class="text-sm text-gray-600 mt-1">{{ __('diving.certification_details_status') }}</p>
        </div>

        <!-- Individual Profile Hero -->
        <div class="max-w-4xl">
            <x-individual.profile-hero :individual="$certification->individual" />

            <!-- Supplementary Info: Gender, Email, Phone -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 -mt-3 mb-6">
                <div class="px-4 sm:px-6 py-3 sm:py-4">
                    <dl class="grid grid-cols-1 sm:grid-cols-3 gap-x-6 gap-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('main.gender') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @if($certification->individual->gender === 'male')
                                    {{ __('main.male') }}
                                @elseif($certification->individual->gender === 'female')
                                    {{ __('main.female') }}
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('main.email') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $certification->individual->email ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('main.phone') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $certification->individual->phone ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Certification Details Card -->
        <div class="card-container max-w-4xl">
            <div class="card">
                <div class="card-header border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-medium text-gray-900">{{ $certification->certification_name }}</h2>
                            <p class="text-sm text-gray-500">
                                @switch($certification->certification_level)
                                    @case('diver_level_3')
                                        {{ __('diving.diver_level_3_dive_leader') }}
                                        @break
                                    @case('instructor_level_1')
                                        {{ __('diving.instructor_level_1') }}
                                        @break
                                    @case('instructor_level_2')
                                        {{ __('diving.instructor_level_2') }}
                                        @break
                                    @case('instructor_level_3')
                                        {{ __('diving.instructor_level_3') }}
                                        @break
                                    @case('first_aid_bls_oxygen')
                                        {{ __('diving.first_aid_bls_oxygen') }}
                                        @break
                                    @case('compressor_operator')
                                        {{ __('diving.compressor_operator') }}
                                        @break
                                    @default
                                        {{ $certification->certification_level }}
                                @endswitch
                            </p>
                        </div>
                        <div>
                            @if($certification->isActive())
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ __('diving.active') }}
                                </span>
                            @elseif($certification->isPendingValidation())
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    {{ __('diving.pending_validation') }}
                                </span>
                            @elseif($certification->isExpired())
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    {{ __('diving.expired') }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $certification->state->name() }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                        <!-- Certification System -->
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('diving.certification_system') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $certification->certification_system }}
                                </span>
                            </dd>
                        </div>

                        <!-- Certification Number -->
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('diving.certification_number') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $certification->certification_number }}</dd>
                        </div>

                        <!-- National Equivalency -->
                        @if($certification->national_equivalency)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('diving.national_equivalency') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @switch($certification->national_equivalency)
                                        @case('diver_level_3')
                                            {{ __('diving.diver_level_3_dive_leader') }}
                                            @break
                                        @case('instructor_level_1')
                                            {{ __('diving.instructor_level_1') }}
                                            @break
                                        @case('instructor_level_2')
                                            {{ __('diving.instructor_level_2') }}
                                            @break
                                        @case('instructor_level_3')
                                            {{ __('diving.instructor_level_3') }}
                                            @break
                                        @case('first_aid_bls_oxygen')
                                            {{ __('diving.first_aid_bls_oxygen') }}
                                            @break
                                        @case('compressor_operator')
                                            {{ __('diving.compressor_operator') }}
                                            @break
                                        @default
                                            {{ $certification->national_equivalency }}
                                    @endswitch
                                </dd>
                            </div>
                        @endif

                        <!-- Issue Date -->
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('diving.issue_date') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $certification->issue_date->format('d/m/Y') }}</dd>
                        </div>

                        <!-- Expiration Date -->
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('diving.expiration_date') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @if($certification->expiration_date)
                                    {{ $certification->expiration_date->format('d/m/Y') }}
                                    @if($certification->expiration_date->isPast())
                                        <span class="ml-2 text-red-600 text-xs">{{ __('diving.status_expired') }}</span>
                                    @elseif($certification->expiration_date->diffInDays(now()) <= 30)
                                        <span class="ml-2 text-yellow-600 text-xs">{{ __('diving.status_expiring_soon') }}</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </dd>
                        </div>

                        <!-- Upload Date -->
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('diving.uploaded_on') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $certification->created_at->format('d/m/Y H:i') }}</dd>
                        </div>

                        <!-- Validation Date -->
                        @if($certification->validated_at)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ __('diving.validated_on') }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $certification->validated_at->format('d/m/Y H:i') }}
                                </dd>
                            </div>
                        @endif

                        <!-- Certificate Document -->
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">{{ __('diving.certificate_document') }}</dt>
                            <dd class="mt-1">
                                @if($certification->getFirstMedia('certificate_documents'))
                                    <a href="{{ $certification->getFirstMediaUrl('certificate_documents') }}"
                                       target="_blank"
                                       class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg class="mr-2 -ml-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        {{ __('diving.download_certificate') }}
                                    </a>
                                @else
                                    <span class="text-gray-500">{{ __('diving.no_document_uploaded') }}</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                @if($certification->isPendingValidation())
                    <div class="card-footer bg-blue-50 border-t border-blue-200">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
                                    {{ __('diving.certification_review_notice') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex justify-between">
            <a href="{{ route('individual.diving_certifications.index') }}" class="btn btn-secondary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                {{ __('diving.back_to_list') }}
            </a>

            @if($certification->canBeValidated() && auth()->user()->can('update', $certification))
                <div class="space-x-3">
                    <a href="{{ route('individual.diving_certifications.edit', $certification) }}" class="btn btn-secondary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        {{ __('Edit') }}
                    </a>

                    @if(auth()->user()->can('delete', $certification))
                        <form action="{{ route('individual.diving_certifications.destroy', $certification) }}"
                              method="POST"
                              class="inline"
                              onsubmit="return confirm('{{ __('Are you sure you want to delete this certification?') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                {{ __('Delete') }}
                            </button>
                        </form>
                    @endif
                </div>
            @endif
        </div>

    </div>
</x-layout>