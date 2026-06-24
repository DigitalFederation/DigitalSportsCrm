@section('title', __('diving.technical_director_invitations_management'))
<x-layout>
    <div class="previous-layout-classes">
        
        <!-- Page header -->
        <div class="mb-8 flex justify-between">
            <div>
                <h1 class="page-first-title">{{ __('diving.technical_director_invitations_management') }}</h1>
                <p class="text-sm text-gray-600 mt-1">{{ __('diving.monitor_technical_director_invitations') }}</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-6">
            <form method="GET" action="{{ route('admin.diving_technical_director_invitations.index') }}" class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="{{ __('diving.search_entity_or_individual') }}"
                           class="form-input w-full">
                </div>
                
                <div class="w-48">
                    <select name="status" class="form-select w-full">
                        <option value="">{{ __('All Statuses') }}</option>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    {{ __('Filter') }}
                </button>
                
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.diving_technical_director_invitations.index') }}" class="btn btn-secondary">
                        {{ __('Clear') }}
                    </a>
                @endif
            </form>
        </div>

        <!-- Invitations Table -->
        <div class="card">
            <div class="overflow-x-auto">
                <table class="table-auto w-full">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left">{{ __('diving.entity') }}</th>
                            <th class="px-4 py-3 text-left">{{ __('diving.invited_professional') }}</th>
                            <th class="px-4 py-3 text-left">{{ __('diving.license') }}</th>
                            <th class="px-4 py-3 text-left">{{ __('diving.certification_system') }}</th>
                            <th class="px-4 py-3 text-left">{{ __('diving.sent_on') }}</th>
                            <th class="px-4 py-3 text-left">{{ __('diving.status') }}</th>
                            <th class="px-4 py-3 text-left">{{ __('diving.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invitations as $invitation)
                            <tr class="border-t">
                                <td class="px-4 py-3">
                                    <div>
                                        <div class="font-medium">{{ $invitation->entity->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $invitation->entity->member_code ?? $invitation->entity->id }}</div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div>
                                        <div class="font-medium">{{ $invitation->individual->full_name }}</div>
                                        <div class="text-sm text-gray-500">{{ $invitation->individual->member_code }}</div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">{{ $invitation->licenseAttributed->license->name }}</td>
                                <td class="px-4 py-3">{{ $invitation->certification_system }}</td>
                                <td class="px-4 py-3">{{ $invitation->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                                          style="background-color: {{ $invitation->state->color() }}20; color: {{ $invitation->state->color() }}">
                                        {{ $invitation->state->name() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.diving_technical_director_invitations.show', $invitation) }}" 
                                       class="text-blue-600 hover:text-blue-800">
                                        {{ __('View') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                    {{ __('diving.no_invitations_found') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="px-4 py-3 border-t">
                {{ $invitations->withQueryString()->links() }}
            </div>
        </div>
    </div>
</x-layout>