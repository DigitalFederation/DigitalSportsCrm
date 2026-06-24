@if(!empty($certificationAttributed->entity))
<div class="card w-full mt-4">

    <div class="flex flex-row justify-between items-center mb-4 border-b border-slate-200">
        <h2 class="text-lg font-bold">{{ __('Entity') }}</h2>
    </div>

    <div class="flex flex-col md:flex-row justify-between">
        <div class="md:w-1/2">
            <div>
                <div class="text-secondary font-semibold">{{ __('Name')}}</div>
                <p class="text-slate-500">
                    {{ ucwords(strtolower($certificationAttributed->entity->name)) }}
                </p>
            </div>


            @if($certificationAttributed->entity->address)
                <div>
                    <div class="text-secondary font-semibold">{{ __('Address')}}</div>
                    <p class="text-slate-500">
                        {{ $certificationAttributed->entity->address }}
                    </p>
                </div>
            @endif

            @if($certificationAttributed->entity->zip_code)
                <div>
                    <div class="text-secondary font-semibold">{{ __('Postal Code')}}</div>
                    <p class="text-slate-500">
                        {{ $certificationAttributed->entity->zip_code }}
                    </p>
                </div>
            @endif

            @if($certificationAttributed->entity->location)
                <div>
                    <div class="text-secondary font-semibold">{{ __('Location')}}</div>
                    <p class="text-slate-500">
                        {{ $certificationAttributed->entity->location }}
                    </p>
                </div>
            @endif
        </div>

        <div class="md:w-1/2">
            @if($certificationAttributed->entity->country)
                <div>
                    <div class="text-secondary font-semibold">{{ __('Country')}}</div>
                    <p class="text-slate-500 flex items-center">
                        <img src="{{ asset('img/flags/' . strtolower($certificationAttributed->entity->country->iso) . '.svg') }}" alt="flag" class="w-4 h-4 mr-1"/>
                        <span>{{ $certificationAttributed->entity->country->name }}</span>
                    </p>
                </div>
            @endif

            @if($certificationAttributed->entity->phone)
                <div>
                    <div class="text-secondary font-semibold">{{ __('Phone')}}</div>
                    <p class="text-slate-500">
                        <span>{{ $certificationAttributed->entity->phone }}</span>
                    </p>
                </div>
            @endif

            @if($certificationAttributed->entity->email)
                <div>
                    <div class="text-secondary font-semibold">{{ __('Email')}}</div>
                    <p class="text-slate-500">
                        <span>{{ $certificationAttributed->entity->email }}</span>
                    </p>
                </div>
            @endif
        </div>
    </div>

</div>
@endif
