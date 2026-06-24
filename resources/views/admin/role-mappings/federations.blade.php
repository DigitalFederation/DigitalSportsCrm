@section('title', __('admin.role_mappings.federation_roles_title'))
<x-layout>
    <div class="previous-layout-classes">

        <!-- Page header -->
        <div class="mb-8">
            
            <!-- Breadcrumb -->
            <nav class="flex mb-2" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('admin.role-mappings.index') }}" class="text-slate-500 hover:text-indigo-600">
                            {{ __('admin.role_mappings.dashboard_title') }}
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-3 h-3 text-slate-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="ml-1 text-slate-500 md:ml-2">{{ __('admin.role_mappings.federation_roles_title') }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <!-- Title -->
            <h1 class="page-first-title">{{ __('admin.role_mappings.federation_roles_title') }}</h1>
        </div>

        <!-- Main content -->
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
            <div class="px-5 py-4">
                <p class="text-slate-600 mb-6">
                    {{ __('admin.role_mappings.federation_roles_help') }}
                </p>
                
                @livewire('admin.federation-role-mapping-table')
            </div>
        </div>

    </div>
</x-layout>