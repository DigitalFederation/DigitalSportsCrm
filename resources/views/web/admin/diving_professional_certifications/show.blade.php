@section('title', __('diving.certification_details'))
<x-layout>
    <div class="previous-layout-classes">
        
        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <div>
                <h1 class="page-first-title">{{ __('diving.certification_details') }}</h1>
                <p class="text-sm text-gray-600 mt-1">{{ __('diving.review_validate_certification') }}</p>
            </div>
            <div>
                <a href="{{ route('admin.diving_professional_certifications.index') }}" class="btn btn-secondary">
                    {{ __('Back to List') }}
                </a>
            </div>
        </div>

        <!-- Individual Profile Hero -->
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Information -->
            <div class="lg:col-span-2">
                <div class="card">
                    <h3 class="text-lg font-semibold mb-4">{{ __('diving.certification_information') }}</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Row 1 -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('diving.status') }}</label>
                            <p class="mt-1">
                                @php
                                    $badgeColor = match($certification->state->name()) {
                                        'active' => 'green',
                                        'pending_validation' => 'yellow',
                                        'expired' => 'gray',
                                        'revoked' => 'red',
                                        default => 'gray',
                                    };
                                @endphp
                                <x-tables.badge :status="__('diving.' . $certification->state->name())" :color="$badgeColor" />
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('diving.national_certification_level') }}</label>
                            <p class="mt-1">
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

                        <!-- Row 2 -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('diving.certification_system') }}</label>
                            <p class="mt-1">{{ $certification->certification_system }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('diving.certification_name') }}</label>
                            <p class="mt-1">{{ $certification->certification_name }}</p>
                        </div>

                        <!-- Row 3 -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('diving.certification_number') }}</label>
                            <p class="mt-1">{{ $certification->certification_number }}</p>
                        </div>

                        <!-- Row 4 -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('diving.issue_date') }}</label>
                            <p class="mt-1">{{ $certification->issue_date->format('d/m/Y') }}</p>
                        </div>

                        <!-- Row 5 -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('diving.expiration_date') }}</label>
                            <p class="mt-1">
                                @if($certification->expiration_date)
                                    {{ $certification->expiration_date->format('d/m/Y') }}
                                    @if($certification->expiration_date->isPast())
                                        <span class="ml-2 text-red-600 text-xs">{{ __('diving.status_expired') }}</span>
                                    @elseif($certification->expiration_date->diffInDays(now()) <= 30)
                                        <span class="ml-2 text-yellow-600 text-xs">{{ __('diving.status_expiring_soon') }}</span>
                                    @endif
                                @else
                                    {{ __('N/A') }}
                                @endif
                            </p>
                        </div>

                        <!-- Row 6 -->
                        @if($certification->validated_at)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('diving.validated_by') }}</label>
                            <p class="mt-1">
                                {{ $certification->validatedBy->name }}<br>
                                <span class="text-sm text-gray-500">{{ $certification->validated_at->format('d/m/Y H:i') }}</span>
                            </p>
                        </div>
                        @endif
                    </div>
                    
                    @if($certification->rejection_reason)
                    <div class="mt-4 p-4 bg-red-50 rounded">
                        <label class="block text-sm font-medium text-red-700">{{ __('diving.rejection_reason') }}</label>
                        <p class="mt-1 text-red-600">{{ $certification->rejection_reason }}</p>
                    </div>
                    @endif
                </div>
                
                <!-- Document -->
                <div class="card mt-6">
                    <h3 class="text-lg font-semibold mb-4">{{ __('diving.certificate_document') }}</h3>
                    
                    @if($certification->getFirstMedia('certificate_documents'))
                        <div class="border rounded p-4">
                            <a href="{{ route('admin.diving_professional_certifications.download_document', $certification) }}"
                               class="flex items-center justify-between hover:bg-gray-50 p-2 rounded">
                                <div class="flex items-center">
                                    <svg class="h-8 w-8 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <div>
                                        <p class="font-medium">{{ $certification->getFirstMedia('certificate_documents')->name }}</p>
                                        <p class="text-sm text-gray-500">{{ $certification->getFirstMedia('certificate_documents')->human_readable_size }}</p>
                                    </div>
                                </div>
                                <span class="text-blue-600">{{ __('diving.download_certificate') }}</span>
                            </a>
                        </div>
                    @else
                        <p class="text-gray-500">{{ __('diving.no_document_uploaded') }}</p>
                    @endif
                </div>
            </div>
            
            <!-- Actions Sidebar -->
            <div class="lg:col-span-1">
                <div class="card">
                    <h3 class="text-lg font-semibold mb-4">{{ __('Actions') }}</h3>
                    
                    <!-- Edit Button - Available for all states -->
                    <button type="button" 
                            onclick="document.getElementById('edit-modal').classList.remove('hidden')"
                            class="btn btn-secondary w-full mb-3">
                        {{ __('diving.edit_certification_details') }}
                    </button>
                    
                    @if($certification->status_class === \Domain\Diving\States\PendingValidationDivingCertificationState::class)
                        <form action="{{ route('admin.diving_professional_certifications.approve', $certification) }}" method="POST" class="mb-3">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-primary w-full">
                                {{ __('diving.approve_certification') }}
                            </button>
                        </form>
                        
                        <button type="button" 
                                onclick="document.getElementById('reject-modal').classList.remove('hidden')"
                                class="btn btn-warning w-full mb-3">
                            {{ __('diving.reject_certification') }}
                        </button>
                    @elseif($certification->status_class === \Domain\Diving\States\ActiveDivingCertificationState::class)
                        <button type="button" 
                                onclick="document.getElementById('revoke-modal').classList.remove('hidden')"
                                class="btn btn-warning w-full mb-3">
                            <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                            </svg>
                            {{ __('diving.revoke_certification') }}
                        </button>
                    @endif
                    
                    <!-- Separator -->
                    <hr class="my-4 border-gray-200">
                    
                    <!-- Delete Button - Available for all states -->
                    <div class="p-3 bg-red-50 rounded-md">
                        <p class="text-xs text-red-600 mb-2">{{ __('diving.delete_warning_permanent') }}</p>
                        <button type="button" 
                                onclick="document.getElementById('delete-modal').classList.remove('hidden')"
                                class="btn btn-danger w-full">
                            <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            {{ __('diving.delete_certification') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Reject Modal -->
    <div id="reject-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('diving.reject_certification') }}</h3>
            
            <form action="{{ route('admin.diving_professional_certifications.reject', $certification) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-4">
                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('diving.rejection_reason') }} <span class="text-red-500">*</span>
                    </label>
                    <textarea id="reason" 
                              name="reason" 
                              rows="3" 
                              class="form-textarea w-full"
                              required></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" 
                            onclick="document.getElementById('reject-modal').classList.add('hidden')"
                            class="btn btn-secondary">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn btn-danger">
                        {{ __('diving.reject') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Revoke Modal -->
    <div id="revoke-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('diving.revoke_certification') }}</h3>
            
            <form action="{{ route('admin.diving_professional_certifications.revoke', $certification) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-4">
                    <label for="revoke_reason" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('diving.revocation_reason') }} <span class="text-red-500">*</span>
                    </label>
                    <textarea id="revoke_reason" 
                              name="reason" 
                              rows="3" 
                              class="form-textarea w-full"
                              required></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" 
                            onclick="document.getElementById('revoke-modal').classList.add('hidden')"
                            class="btn btn-secondary">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn btn-danger">
                        {{ __('diving.revoke') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Delete Modal -->
    <div id="delete-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('diving.delete_certification') }}</h3>
            
            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-3">
                    {{ __('diving.delete_confirmation_message') }}
                </p>
                <div class="bg-yellow-50 border border-yellow-200 rounded p-3">
                    <p class="text-sm text-yellow-800">
                        <strong>{{ __('Warning') }}:</strong> {{ __('diving.delete_warning_message') }}
                    </p>
                </div>
            </div>
            
            <form action="{{ route('admin.diving_professional_certifications.destroy', $certification) }}" method="POST">
                @csrf
                @method('DELETE')
                
                <div class="flex justify-end space-x-3">
                    <button type="button" 
                            onclick="document.getElementById('delete-modal').classList.add('hidden')"
                            class="btn btn-secondary">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn btn-danger">
                        {{ __('diving.delete') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit Modal -->
    <div id="edit-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-[600px] shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('diving.edit_certification_details') }}</h3>
            
            <form action="{{ route('admin.diving_professional_certifications.update', $certification) }}" method="POST">
                @csrf
                @method('PATCH')
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="certification_name" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('diving.certification_name') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="certification_name"
                               name="certification_name"
                               value="{{ $certification->certification_name }}"
                               class="form-input w-full"
                               required>
                    </div>

                    <div>
                        <label for="certification_system" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('diving.certification_system') }} <span class="text-red-500">*</span>
                        </label>
                        <select id="certification_system"
                                name="certification_system"
                                class="form-select w-full"
                                required>
                            <option value="">{{ __('diving.select_certification_system') }}</option>
                            @foreach (config('diving.certification_systems') as $system)
                                <option value="{{ $system }}" {{ $certification->certification_system == $system ? 'selected' : '' }}>
                                    {{ $system }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="certification_number" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('diving.certification_number') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="certification_number"
                               name="certification_number"
                               value="{{ $certification->certification_number }}"
                               class="form-input w-full"
                               required>
                    </div>

                    <div>
                        <label for="certification_level" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('diving.national_certification_level') }} <span class="text-red-500">*</span>
                        </label>
                        <select id="certification_level"
                                name="certification_level"
                                class="form-select w-full"
                                required>
                            <option value="">{{ __('diving.select_certification_level') }}</option>
                            <option value="diver_level_3" {{ $certification->certification_level == 'diver_level_3' ? 'selected' : '' }}>
                                {{ __('diving.diver_level_3_dive_leader') }}
                            </option>
                            <option value="instructor_level_1" {{ $certification->certification_level == 'instructor_level_1' ? 'selected' : '' }}>
                                {{ __('diving.instructor_level_1') }}
                            </option>
                            <option value="instructor_level_2" {{ $certification->certification_level == 'instructor_level_2' ? 'selected' : '' }}>
                                {{ __('diving.instructor_level_2') }}
                            </option>
                            <option value="instructor_level_3" {{ $certification->certification_level == 'instructor_level_3' ? 'selected' : '' }}>
                                {{ __('diving.instructor_level_3') }}
                            </option>
                            <option value="first_aid_bls_oxygen" {{ $certification->certification_level == 'first_aid_bls_oxygen' ? 'selected' : '' }}>
                                {{ __('diving.first_aid_bls_oxygen') }}
                            </option>
                            <option value="compressor_operator" {{ $certification->certification_level == 'compressor_operator' ? 'selected' : '' }}>
                                {{ __('diving.compressor_operator') }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="issue_date" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('diving.issue_date') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               id="issue_date" 
                               name="issue_date" 
                               value="{{ $certification->issue_date->format('Y-m-d') }}"
                               class="form-input w-full"
                               required>
                    </div>
                    
                    <div>
                        <label for="expiration_date" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('diving.expiration_date') }}
                        </label>
                        <input type="date" 
                               id="expiration_date" 
                               name="expiration_date" 
                               value="{{ $certification->expiration_date ? $certification->expiration_date->format('Y-m-d') : '' }}"
                               class="form-input w-full">
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" 
                            onclick="document.getElementById('edit-modal').classList.add('hidden')"
                            class="btn btn-secondary">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        {{ __('diving.save_changes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layout>