@php
    $stateName = $certification->stateName();
    $translatedState = __('certifications.details.states.' . $stateName);
    // Fall back to ucfirst if no translation exists
    if ($translatedState === 'certifications.details.states.' . $stateName) {
        $translatedState = ucfirst($stateName);
    }

    // Status color mapping with enhanced contrast
    $statusColors = match($stateName) {
        'active' => ['bg' => 'bg-emerald-600', 'text' => 'text-emerald-700', 'light' => 'bg-emerald-50', 'border' => 'border-emerald-200'],
        'pending' => ['bg' => 'bg-amber-500', 'text' => 'text-amber-700', 'light' => 'bg-amber-50', 'border' => 'border-amber-200'],
        'suspended' => ['bg' => 'bg-red-600', 'text' => 'text-red-700', 'light' => 'bg-red-50', 'border' => 'border-red-200'],
        'rejected' => ['bg' => 'bg-slate-600', 'text' => 'text-slate-700', 'light' => 'bg-slate-50', 'border' => 'border-slate-200'],
        'expired' => ['bg' => 'bg-orange-600', 'text' => 'text-orange-700', 'light' => 'bg-orange-50', 'border' => 'border-orange-200'],
        'provisional' => ['bg' => 'bg-sky-500', 'text' => 'text-sky-700', 'light' => 'bg-sky-50', 'border' => 'border-sky-200'],
        default => ['bg' => 'bg-blue-600', 'text' => 'text-blue-700', 'light' => 'bg-blue-50', 'border' => 'border-blue-200'],
    };
@endphp
<div class="relative bg-white shadow-md rounded-xl overflow-hidden border border-gray-100">
    {{-- Card Header with Title and Status Ribbon --}}
    <div class="py-5 border-b border-gray-200 px-6 bg-gradient-to-r from-gray-50 to-white">
        <h2 class="text-lg font-bold text-gray-800">
            {{ __('certifications.details.title') }}
        </h2>
        {{-- Enhanced Corner Ribbon --}}
        <div class="absolute top-0 right-0 h-24 w-24">
            <div class="animate-ribbon-appear absolute transform right-[-40px] top-[20px] w-[170px] rotate-45 shadow-lg py-1.5 text-center {{ $statusColors['bg'] }}">
                <span class="text-white font-semibold tracking-wider text-sm drop-shadow-sm">
                    {{ $translatedState }}
                </span>
            </div>
        </div>
    </div>

    <div class="px-6 py-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Left Column -->
            <div class="space-y-5">
                <!-- Certification -->
                <div class="group">
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                        {{ __('certifications.details.certification') }}
                    </dt>
                    <dd class="text-base font-medium text-gray-900">
                        {{ empty($certification->name) ? $certification->certification->name : $certification->name }}
                    </dd>
                </div>

                <!-- Student -->
                <div class="group">
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                        {{ __('certifications.details.student') }}
                    </dt>
                    <dd class="text-base font-medium text-gray-900">
                        @if($certification->individual)
                            {{ $certification->individual->name }} {{ $certification->individual->surname }}
                        @else
                            {{ $certification->holder_name }}
                        @endif
                    </dd>
                </div>

                <!-- National Certification Number (if available) -->
                @if(!empty($certification->national_code))
                    <div class="group">
                        <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                            {{ __('certifications.details.national_certification_number') }}
                        </dt>
                        <dd class="text-base font-medium text-gray-900 font-mono">
                            {{ $certification->national_code }}
                        </dd>
                    </div>
                @endif
            </div>

            <!-- Right Column -->
            <div class="space-y-5">
                <!-- Issue Date -->
                <div class="group">
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                        {{ __('certifications.details.issue_date') }}
                    </dt>
                    <dd class="text-base font-medium text-gray-900">
                        {{ $certification->current_term_starts_at ? Carbon\Carbon::parse($certification->current_term_starts_at)->format('d-m-Y') : '---' }}
                    </dd>
                </div>

                <!-- Expire Date -->
                <div class="group">
                    <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                        {{ __('certifications.details.expire_date') }}
                    </dt>
                    <dd class="text-base font-medium text-gray-900">
                        @if(empty($certification->current_term_ends_at))
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                {{ __('certifications.details.no_expiration_date') }}
                            </span>
                        @else
                            {{ Carbon\Carbon::parse($certification->current_term_ends_at)->format('d-m-Y') }}
                        @endif
                    </dd>
                </div>

                <!-- Approved date (if applicable) -->
                @if($certification->activated_at)
                    <div class="group">
                        <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">
                            {{ __('certifications.details.approved_date') }}
                        </dt>
                        <dd class="text-base font-medium text-gray-900">
                            {{ Carbon\Carbon::parse($certification->activated_at)->format('d-m-Y') }}
                        </dd>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        @keyframes ribbonAppear {
            0% {
                opacity: 0;
                transform: rotate(45deg) translateY(-100px);
            }
            50% {
                opacity: 0.5;
                transform: rotate(45deg) translateY(10px);
            }
            75% {
                transform: rotate(45deg) translateY(-5px);
            }
            100% {
                opacity: 1;
                transform: rotate(45deg) translateY(0);
            }
        }

        .animate-ribbon-appear {
            animation: ribbonAppear 0.8s ease-out forwards;
        }
    </style>
</div>
