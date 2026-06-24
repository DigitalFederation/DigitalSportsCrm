{{-- resources/views/components/impersonation-bar.blade.php --}}
@if($isImpersonating)
    <div class="flex items-center space-x-3">
        <div class="flex items-center px-3 py-1.5 bg-red-50 rounded-lg text-red-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z" clip-rule="evenodd" />
            </svg>
            <span class="text-sm font-medium">
                Impersonating: {{ $impersonatedUser->name }} ({{ ucfirst(strtolower($impersonatedUser->group->code)) }})
            </span>
        </div>
        <a href="{{ route('impersonate.stop') }}" class="px-3 py-1.5 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors">
            Exit
        </a>
    </div>
@endif
