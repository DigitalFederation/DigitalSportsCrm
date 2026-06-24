<x-layout>
    <div class="previous-layout-classes">
        <!-- Page Header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5 mt-5">
            <div class="mb-4 sm:mb-0">
                <div class="flex items-center gap-4">
                    @if($federation->getFirstMediaUrl('logo'))
                        <img
                            class="h-12 w-12 object-cover rounded-full border-2 border-slate-200"
                            src="{{ $federation->getFirstMediaUrl('logo') }}"
                            alt="{{ $federation->name }}"
                        >
                    @endif
                    <div>
                        <h1 class="page-first-title">{{ $federation->legal_name ?? $federation->name }}</h1>
                        <p class="text-sm text-slate-500">{{ __('menu.federation_details') }}</p>
                    </div>
                </div>
            </div>
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a href="{{ route('individual.federation.index') }}" class="btn btn-secondary">
                    {{ __('main.Back') }}
                </a>
            </div>
        </div>

        <!-- Two Column Layout: Data | Contact Information -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
            <!-- Data Block -->
            <div class="bg-white border border-slate-200 rounded-lg p-5">
                <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    {{ __('main.Data') }}
                </h3>
                <dl class="space-y-4">
                    <!-- Designation -->
                    <div>
                        <dt class="text-xs font-medium text-slate-500 uppercase tracking-wide">{{ __('main.Designation') }}</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-800">{{ $federation->legal_name ?? $federation->name }}</dd>
                    </div>

                    <!-- NIF -->
                    <div>
                        <dt class="text-xs font-medium text-slate-500 uppercase tracking-wide">{{ __('main.NIF') }}</dt>
                        <dd class="mt-1 text-sm text-slate-700">
                            {{ $federation->vat_number ?? __('main.Not available') }}
                        </dd>
                    </div>

                    <!-- Address -->
                    <div>
                        <dt class="text-xs font-medium text-slate-500 uppercase tracking-wide">{{ __('main.Address') }}</dt>
                        <dd class="mt-1">
                            @if($federation->address || $federation->location || $federation->zip_code || $federation->district)
                                <address class="not-italic text-sm text-slate-700 leading-relaxed">
                                    @if($federation->address)
                                        <span class="block">{{ $federation->address }}</span>
                                    @endif
                                    @if($federation->location || $federation->zip_code)
                                        <span class="block">
                                            @if($federation->zip_code){{ $federation->zip_code }}@endif
                                            @if($federation->zip_code && $federation->location), @endif
                                            @if($federation->location){{ $federation->location }}@endif
                                        </span>
                                    @endif
                                    @if($federation->district)
                                        <span class="block text-slate-600">{{ $federation->district->name }}</span>
                                    @endif
                                </address>
                            @else
                                <span class="text-sm text-slate-400 italic">{{ __('main.Not available') }}</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Contact Information Block -->
            <div class="bg-white border border-slate-200 rounded-lg p-5">
                <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    {{ __('main.Contact Information') }}
                </h3>
                <div class="space-y-4">
                    <!-- Email -->
                    <div class="flex items-start gap-3 p-3 bg-slate-50 rounded-lg">
                        <div class="flex-shrink-0 w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wide">{{ __('main.Email') }}</dt>
                            @if($federation->email)
                                <dd class="mt-1">
                                    <a href="mailto:{{ $federation->email }}" class="text-sm text-primary hover:text-primary-light font-medium truncate block">
                                        {{ $federation->email }}
                                    </a>
                                </dd>
                            @else
                                <dd class="mt-1 text-sm text-slate-400 italic">{{ __('main.Not available') }}</dd>
                            @endif
                        </div>
                    </div>

                    <!-- Phone -->
                    <div class="flex items-start gap-3 p-3 bg-slate-50 rounded-lg">
                        <div class="flex-shrink-0 w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wide">{{ __('main.Phone') }}</dt>
                            @if($federation->phone)
                                <dd class="mt-1">
                                    <a href="tel:{{ $federation->phone }}" class="text-sm text-slate-800 font-medium hover:text-emerald-600">
                                        {{ $federation->phone }}
                                    </a>
                                </dd>
                            @else
                                <dd class="mt-1 text-sm text-slate-400 italic">{{ __('main.Not available') }}</dd>
                            @endif
                        </div>
                    </div>

                    <!-- Website -->
                    <div class="flex items-start gap-3 p-3 bg-slate-50 rounded-lg">
                        <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <dt class="text-xs font-medium text-slate-500 uppercase tracking-wide">{{ __('main.Website') }}</dt>
                            @if($federation->website)
                                <dd class="mt-1">
                                    <a href="{{ $federation->website }}" target="_blank" rel="noopener noreferrer" class="text-sm text-blue-600 hover:text-blue-800 font-medium inline-flex items-center gap-1">
                                        {{ $federation->website }}
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                        </svg>
                                    </a>
                                </dd>
                            @else
                                <dd class="mt-1 text-sm text-slate-400 italic">{{ __('main.Not available') }}</dd>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
