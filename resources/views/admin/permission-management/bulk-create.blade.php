<x-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <!-- Page header -->
        <div class="mb-8">
            <h1 class="text-2xl md:text-3xl text-gray-800 font-bold">{{ __('permission_management.bulk_create') }}</h1>
        </div>

        <div class="card p-6">
            
            <!-- Form -->
            <form action="{{ route('admin.permission-management.bulk-store') }}" method="POST" id="bulk-create-form">
                @csrf

                <!-- Default settings -->
                <div class="mb-6 p-4 bg-gray-50 rounded">
                    <h3 class="text-sm font-medium text-gray-800 mb-3">{{ __('permission_management.apply_defaults') }}</h3>
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-full sm:col-span-6">
                            <label class="block text-sm font-medium mb-1" for="default_category">
                                {{ __('permission_management.default_category') }}
                            </label>
                            <select id="default_category" name="default_category" class="form-select w-full">
                                <option value="">{{ __('permission_management.select_category') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}">{{ ucfirst($category) }}</option>
                                @endforeach
                                <option value="new">{{ __('permission_management.new_category') }}</option>
                            </select>
                        </div>
                        <div class="col-span-full sm:col-span-6">
                            <label class="block text-sm font-medium mb-1" for="default_guard">
                                {{ __('permission_management.default_guard') }}
                            </label>
                            <select id="default_guard" name="default_guard" class="form-select w-full">
                                <option value="web" selected>web</option>
                                <option value="api">api</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Permissions table -->
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-sm font-medium text-gray-800">{{ __('permission_management.permissions') }}</h3>
                        <button type="button" onclick="addPermissionRow()" class="btn btn-sm btn-primary">
                            <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                                <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                            </svg>
                            <span class="ml-2">{{ __('permission_management.add_permission_line') }}</span>
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="table-auto w-full">
                            <thead class="text-xs font-semibold uppercase text-gray-500 bg-gray-50 border-t border-b border-gray-200">
                                <tr>
                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-left">{{ __('permission_management.name') }} *</div>
                                    </th>
                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-left">{{ __('permission_management.description') }}</div>
                                    </th>
                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-left">{{ __('permission_management.category') }}</div>
                                    </th>
                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                        <div class="font-semibold text-center">{{ __('common.actions') }}</div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="permissions-tbody" class="text-sm divide-y divide-slate-200">
                                <!-- Permission rows will be added here -->
                            </tbody>
                        </table>
                    </div>

                    <div id="no-permissions-message" class="text-center py-8 text-gray-400">
                        <p>{{ __('permission_management.messages.no_permissions_added') }}</p>
                        <p class="text-sm mt-2">{{ __('permission_management.help.bulk_create') }}</p>
                    </div>
                </div>

                <!-- Form footer -->
                <div class="flex flex-col md:flex-row md:items-center md:justify-between pt-6 border-t">
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
                        <button type="submit" class="btn btn-primary" id="submit-button" disabled>
                            {{ __('permission_management.create') }}
                        </button>
                    </div>
                </div>

            </form>

        </div>

    </div>

    <script>
        let rowCount = 0;

        function addPermissionRow() {
            rowCount++;
            const tbody = document.getElementById('permissions-tbody');
            const row = document.createElement('tr');
            row.id = `permission-row-${rowCount}`;
            
            row.innerHTML = `
                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                    <input type="text" 
                           name="permissions[${rowCount}][name]" 
                           class="form-input w-full min-w-[200px]" 
                           placeholder="manage-something"
                           required
                           onchange="validateForm()">
                </td>
                <td class="px-2 first:pl-5 last:pr-5 py-3">
                    <input type="text" 
                           name="permissions[${rowCount}][description]" 
                           class="form-input w-full min-w-[300px]" 
                           placeholder="{{ __('permission_management.description') }}">
                </td>
                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                    <input type="text" 
                           name="permissions[${rowCount}][category]" 
                           class="form-input w-full min-w-[150px]" 
                           placeholder="{{ __('permission_management.category') }}">
                </td>
                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                    <button type="button" 
                            onclick="removePermissionRow(${rowCount})" 
                            class="text-rose-500 hover:text-rose-600 rounded-full">
                        <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32">
                            <path d="M13 15h2v6h-2zM17 15h2v6h-2z" />
                            <path d="M20 9c0-.6-.4-1-1-1h-6c-.6 0-1 .4-1 1v2H8v2h1v10c0 .6.4 1 1 1h12c.6 0 1-.4 1-1V13h1v-2h-4V9zm-6 1h4v1h-4v-1zm7 3v9H11v-9h10z" />
                        </svg>
                    </button>
                </td>
            `;
            
            tbody.appendChild(row);
            document.getElementById('no-permissions-message').style.display = 'none';
            validateForm();
        }

        function removePermissionRow(id) {
            const row = document.getElementById(`permission-row-${id}`);
            if (row) {
                row.remove();
            }
            
            const tbody = document.getElementById('permissions-tbody');
            if (tbody.children.length === 0) {
                document.getElementById('no-permissions-message').style.display = 'block';
            }
            validateForm();
        }

        function validateForm() {
            const tbody = document.getElementById('permissions-tbody');
            const submitButton = document.getElementById('submit-button');
            submitButton.disabled = tbody.children.length === 0;
        }

        // Add initial row on page load
        document.addEventListener('DOMContentLoaded', function() {
            addPermissionRow();
        });
    </script>
</x-layout>