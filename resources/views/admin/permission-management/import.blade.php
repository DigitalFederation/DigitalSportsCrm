<x-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <!-- Page header -->
        <div class="mb-8">
            <h1 class="text-2xl md:text-3xl text-gray-800 font-bold">{{ __('permission_management.import_permissions') }}</h1>
        </div>

        <div class="card p-6">
            
            <!-- Import instructions -->
            <div class="mb-6 p-4 bg-gray-50 rounded">
                <h3 class="text-sm font-medium text-gray-800 mb-3">{{ __('permission_management.import') }} {{ __('common.instructions') }}</h3>
                <div class="text-sm text-gray-600 space-y-2">
                    <p>{{ __('permission_management.help.import_format') }}</p>
                    <p class="font-mono text-xs bg-white p-2 rounded">name,category,description,guard_name</p>
                    <p>{{ __('common.example') }}:</p>
                    <div class="font-mono text-xs bg-white p-2 rounded space-y-1">
                        <p>manage-users,Users,Create and manage users,web</p>
                        <p>view-reports,Reports,View system reports,web</p>
                        <p>access-api,API,Access API endpoints,api</p>
                    </div>
                </div>
            </div>

            <!-- Download template -->
            <div class="mb-6">
                <a href="data:text/csv;charset=utf-8,name,category,description,guard_name%0Aexample-permission,Category,Description here,web" 
                   download="permission_import_template.csv"
                   class="inline-flex items-center text-blue-600 hover:text-blue-700">
                    <svg class="w-4 h-4 fill-current mr-2" viewBox="0 0 16 16">
                        <path d="M15 7h-3V1c0-.6-.4-1-1-1H5c-.6 0-1 .4-1 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h3v6c0 .6.4 1 1 1h6c.6 0 1-.4 1-1V9h3c.6 0 1-.4 1-1s-.4-1-1-1zM6 2h4v5H6V2zm0 13V9h4v6H6z" />
                    </svg>
                    {{ __('permission_management.download_template') }}
                </a>
            </div>

            <!-- Form -->
            <form action="{{ route('admin.permission-management.process-import') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="space-y-6">

                    <!-- File upload -->
                    <div>
                        <label class="block text-sm font-medium mb-1" for="file">
                            {{ __('permission_management.select_file') }} <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="file" 
                            id="file" 
                            name="file" 
                            accept=".csv,.txt"
                            class="block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-full file:border-0
                                file:text-sm file:font-medium
                                file:bg-indigo-50 file:text-indigo-700
                                hover:file:bg-indigo-100
                                @error('file') border-rose-300 @enderror"
                            required
                        />
                        <div class="text-xs text-gray-500 mt-1">{{ __('common.max_file_size') }}: 2MB</div>
                        @error('file')
                            <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Import options -->
                    <div class="space-y-3">
                        <h3 class="text-sm font-medium text-gray-800">{{ __('common.options') }}</h3>
                        
                        <label class="flex items-center">
                            <input type="checkbox" name="skip_existing" value="1" checked class="form-checkbox" />
                            <span class="ml-2 text-sm">{{ __('common.skip_existing_records') }}</span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="checkbox" name="validate_only" value="1" class="form-checkbox" />
                            <span class="ml-2 text-sm">{{ __('common.validate_only') }} ({{ __('common.no_import') }})</span>
                        </label>
                    </div>

                </div>

                <!-- Form footer -->
                <div class="flex flex-col md:flex-row md:items-center md:justify-between mt-6 pt-6 border-t">
                    <div class="mb-4 md:mb-0">
                        <a href="{{ route('admin.permission-management.index') }}" class="text-blue-600 hover:underline">
                            &larr; {{ __('permission_management.back_to_list') }}
                        </a>
                    </div>
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('admin.permission-management.index') }}" 
                           class="btn btn-secondary">
                            {{ __('common.cancel') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <svg class="w-4 h-4 fill-current opacity-50 shrink-0 mr-2" viewBox="0 0 16 16">
                                <path d="M15 7h-3V1c0-.6-.4-1-1-1H5c-.6 0-1 .4-1 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h3v6c0 .6.4 1 1 1h6c.6 0 1-.4 1-1V9h3c.6 0 1-.4 1-1s-.4-1-1-1z" />
                            </svg>
                            {{ __('permission_management.import') }}
                        </button>
                    </div>
                </div>

            </form>

        </div>

        <!-- Import history (optional) -->
        @if(session('import_results'))
            <div class="mt-6 card p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('common.import_results') }}</h3>
                
                <div class="space-y-2">
                    <p class="text-sm">
                        <span class="font-medium text-emerald-600">{{ session('import_results.created') }}</span> {{ __('common.created') }}
                    </p>
                    <p class="text-sm">
                        <span class="font-medium text-amber-600">{{ session('import_results.skipped') }}</span> {{ __('common.skipped') }}
                    </p>
                    
                    @if(!empty(session('import_results.errors')))
                        <div class="mt-4">
                            <h4 class="text-sm font-medium text-rose-600 mb-2">{{ __('common.errors') }}:</h4>
                            <ul class="list-disc list-inside text-sm text-rose-600 space-y-1">
                                @foreach(session('import_results.errors') as $error)
                                    <li>{{ $error['permission'] }}: {{ $error['error'] }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        @endif

    </div>
</x-layout>