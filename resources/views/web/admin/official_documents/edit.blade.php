@section('title', __('official_documents.edit_document'))

<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-4">
            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('official_documents.edit_document') }}</h1>
            </div>

            <!-- Right: Back button -->
            <div>
                <a href="{{ route('admin.official-documents.index', $documentType) }}" class="btn btn-secondary">
                    {{ __('official_documents.back_to_list') }}
                </a>
            </div>
        </div>

        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <form id="official-document-edit-form" method="POST" action="{{ route('admin.official-documents.update', $document->id) }}" novalidate>
                @csrf
                @method('PUT')
                
                <input type="hidden" name="document_type" value="{{ $documentType }}">
                
                @if($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">
                                    {{ __('official_documents.form_errors') }}
                                </h3>
                                <div class="mt-2 text-sm text-red-700">
                                    <ul class="list-disc pl-5 space-y-1">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                
                <div class="px-4 py-5 sm:p-6">
                    <!-- Document Info Section -->
                    <div class="mb-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            {{ __('official_documents.document_information') }}
                        </h3>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <!-- Document Name -->
                            @php
                                $documentName = $document->name
                                    ?? $document->media->first()?->name
                                    ?? \App\Enums\OfficialDocumentTypeEnum::toString($document->type);
                            @endphp
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">
                                    {{ __('official_documents.document_name') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
                                       name="name"
                                       id="name"
                                       value="{{ old('name', $documentName) }}"
                                       required
                                       class="form-input mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Document Type -->
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700">
                                    {{ __('official_documents.type') }} <span class="text-red-500">*</span>
                                </label>
                                <select name="type"
                                        id="type"
                                        required
                                        class="form-select mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @foreach($types as $value => $label)
                                        <option value="{{ $value }}" {{ old('type', $document->type->value) == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Federation -->
                            @if($documentType !== 'entity')
                            <div>
                                <label for="federation_id" class="block text-sm font-medium text-gray-700">
                                    {{ __('official_documents.federation') }}
                                </label>
                                <select name="federation_id"
                                        id="federation_id"
                                        class="form-select mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">{{ __('official_documents.select_federation') }}</option>
                                    @foreach($federations as $id => $code)
                                        <option value="{{ $id }}" {{ old('federation_id', $document->federation_id) == $id ? 'selected' : '' }}>
                                            {{ $code }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('federation_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            @endif

                            <!-- Country -->
                            <div>
                                <label for="country_id" class="block text-sm font-medium text-gray-700">
                                    {{ __('official_documents.country') }}
                                </label>
                                <select name="country_id"
                                        id="country_id"
                                        class="form-select mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">{{ __('official_documents.select_country') }}</option>
                                    @foreach($countries as $id => $name)
                                        <option value="{{ $id }}" {{ old('country_id', $document->country_id) == $id ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('country_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Role (read-only for individual documents) -->
                            @if($documentType === 'individual' && $document->role)
                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    {{ __('official_documents.role') }}
                                </label>
                                <p class="mt-1 text-sm text-gray-900 py-2 px-3 bg-gray-100 rounded-md border border-gray-200">
                                    {{ $document->roleLabel() }}
                                </p>
                            </div>
                            @endif

                            <!-- Issue Date -->
                            <div>
                                <label for="issue_date" class="block text-sm font-medium text-gray-700">
                                    {{ __('official_documents.issue_date') }}
                                </label>
                                <input type="date"
                                       name="issue_date"
                                       id="issue_date"
                                       value="{{ old('issue_date', $document->issue_date ? \Carbon\Carbon::parse($document->issue_date)->format('Y-m-d') : '') }}"
                                       class="form-input mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                @error('issue_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Expiry Date -->
                            <div>
                                <label for="expiry_date" class="block text-sm font-medium text-gray-700">
                                    {{ __('official_documents.expiration_date') }}
                                </label>
                                <input type="date"
                                       name="expiry_date"
                                       id="expiry_date"
                                       value="{{ old('expiry_date', $document->expiry_date ? \Carbon\Carbon::parse($document->expiry_date)->format('Y-m-d') : '') }}"
                                       class="form-input mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                @error('expiry_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Owner Information Section -->
                    <div class="mb-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            {{ __('official_documents.owner_information') }}
                        </h3>

                        <div class="bg-gray-50 px-4 py-5 sm:p-6 rounded-md">
                            @if($documentType === 'entity' && $document->owner)
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">{{ __('official_documents.entity_name') }}:</span>
                                        <p class="mt-1 text-sm text-gray-900">{{ $document->owner->name }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">{{ __('official_documents.entity_code') }}:</span>
                                        <p class="mt-1 text-sm text-gray-900">{{ $document->owner->member_code }}</p>
                                    </div>
                                </div>
                            @elseif($documentType === 'individual' && $document->individual)
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">{{ __('official_documents.individual_name') }}:</span>
                                        <p class="mt-1 text-sm text-gray-900">{{ $document->individual->full_name }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm font-medium text-gray-500">{{ __('official_documents.member_code') }}:</span>
                                        <p class="mt-1 text-sm text-gray-900">{{ $document->individual->member_code }}</p>
                                    </div>
                                </div>
                            @elseif($documentType === 'federation' && $document->federation)
                                <div>
                                    <span class="text-sm font-medium text-gray-500">{{ __('official_documents.federation') }}:</span>
                                    <p class="mt-1 text-sm text-gray-900">{{ $document->federation->member_code }} - {{ $document->federation->name }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Document Status Section -->
                    <div class="mb-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            {{ __('official_documents.document_status') }}
                        </h3>

                        <div class="bg-gray-50 px-4 py-5 sm:p-6 rounded-md">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <span class="text-sm font-medium text-gray-500">{{ __('official_documents.current_status') }}:</span>
                                    <p class="mt-1">
                                        <x-tables.badge :status="ucfirst($document->stateName())" :color="$document->stateColor()" />
                                    </p>
                                </div>
                                @if($document->activated_at)
                                <div>
                                    <span class="text-sm font-medium text-gray-500">{{ __('official_documents.activated_at') }}:</span>
                                    <p class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($document->activated_at)->format('d/m/Y H:i') }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Attached Files Section -->
                    @if($document->media->count() > 0)
                    <div class="mb-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            {{ __('official_documents.attached_files') }}
                        </h3>

                        <div class="bg-gray-50 px-4 py-5 sm:p-6 rounded-md">
                            <ul class="divide-y divide-gray-200">
                                @foreach($document->media as $media)
                                <li class="py-3 flex justify-between items-center">
                                    <div class="flex items-center">
                                        <svg class="h-5 w-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <span class="text-sm text-gray-900">{{ $media->file_name }}</span>
                                    </div>
                                    <a href="{{ route('admin.official-documents.download', $document->id) }}"
                                       onclick="event.preventDefault(); downloadDocument('{{ $document->id }}');"
                                       class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                        {{ __('official_documents.download') }}
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Form Actions -->
                <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                    <a href="{{ route('admin.official-documents.index', $documentType) }}" class="btn btn-secondary">
                        {{ __('official_documents.cancel') }}
                    </a>
                    <button type="submit" class="btn btn-primary ml-3">
                        {{ __('official_documents.update_document') }}
                    </button>
                </div>
            </form>
        </div>

    </div>
    
    @push('scripts')
    <script>
        function downloadDocument(documentId) {
            // Create a form dynamically to submit the download request
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("admin.official-documents.download", ":id") }}'.replace(':id', documentId);
            
            // Add CSRF token
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            // Append to body and submit
            document.body.appendChild(form);
            form.submit();
            
            // Clean up
            setTimeout(() => {
                document.body.removeChild(form);
            }, 100);
        }
    </script>
    @endpush
</x-layout>