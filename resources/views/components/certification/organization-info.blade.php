{{-- components/certification/organization-info.blade.php --}}
<div class="bg-white rounded-lg shadow divide-y divide-gray-200">
    {{-- Header --}}
    <div class="px-6 py-4">
        <h2 class="text-lg font-bold text-gray-900">{{ __('certifications.organization_info.title') }}</h2>
    </div>

    {{-- Federation Details --}}
    <div class="px-6 py-4">
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
            {{-- Federation Name --}}
            <div>
                <dt class="text-sm font-medium text-gray-500">
                    {{ __('certifications.organization_info.federation') }}
                </dt>
                <dd class="mt-1 text-sm text-gray-900">
                    {{ $federation->name }}
                </dd>
            </div>

            {{-- Member Code --}}
            <div>
                <dt class="text-sm font-medium text-gray-500">
                    {{ __('main.Member Code') }}
                </dt>
                <dd class="mt-1 text-sm text-gray-900">
                    {{ $federation->member_code }}
                </dd>
            </div>

            {{-- Country --}}
            <div>
                <dt class="text-sm font-medium text-gray-500">
                    {{ __('certifications.organization_info.country') }}
                </dt>
                <dd class="mt-1 flex items-center">
                    @if($country)
                        <img src="{{ asset('img/flags/' . strtolower($country->iso) . '.svg') }}"
                             alt="{{ $country->name }} flag"
                             class="h-4 w-6 mr-2">
                        <span class="text-sm text-gray-900">{{ $country->name }}</span>
                    @else
                        <span class="text-sm text-gray-500">{{ __('certifications.organization_info.not_specified') }}</span>
                    @endif
                </dd>
            </div>

        </dl>
    </div>


    {{-- Approval Information --}}
    @if($certification->activator)
        <div class="px-6 py-4 bg-gray-50">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">
                        {{ __('certifications.organization_info.approved_by') }}
                    </dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $certification->activator->name }}
                    </dd>
                </div>

                @if($certification->activated_at)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">
                            {{ __('certifications.organization_info.approval_date') }}
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ date('d/m/Y', strtotime($certification->activated_at)) }}
                        </dd>
                    </div>
                @endif
            </dl>
        </div>
    @endif
</div>
