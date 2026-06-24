@if(!empty($certificationAttributed->federation))
    <div class="card w-full mt-4" x-data="{ cardOpen: window.innerWidth > 640 }">

        <div class="flex flex-row justify-between items-center">
            <h2 class="text-lg font-bold">{{ __('Organization') }}</h2>
            <div class="flex-1">
                <button type="button" x-on:click="cardOpen = !cardOpen"
                        class="flex justify-end w-full text-right text-slate-500">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         :class="cardOpen ? 'rotate-180' : 'rotate-0'"
                         class="h-6 w-6" width="16" height="16"
                         viewBox="0 0 24 24"
                         stroke-width="1.5" stroke="#9e9e9e" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <polyline points="6 9 12 15 18 9" />
                    </svg>
                </button>
            </div>
        </div>

        <div x-show="cardOpen">
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2  gap-y-2 justify-between">


                <div>
                    <div class="text-secondary font-semibold">{{ __('Name')}}</div>
                    <p class="text-slate-500">
                        {{ ucwords(strtolower($certificationAttributed->federation->name)) }}
                    </p>
                </div>

                @if($certificationAttributed->federation->address)
                    <div>
                        <div class="text-secondary font-semibold">{{ __('Address')}}</div>
                        <p class="text-slate-500">
                            {{ $certificationAttributed->federation->address }}
                        </p>
                    </div>
                @endif

                @if($certificationAttributed->federation->zip_code)
                    <div>
                        <div class="text-secondary font-semibold">{{ __('Postal Code')}}</div>
                        <p class="text-slate-500">
                            {{ $certificationAttributed->federation->zip_code }}
                        </p>
                    </div>
                @endif

                @if($certificationAttributed->federation->location)
                    <div>
                        <div class="text-secondary font-semibold">{{ __('Location')}}</div>
                        <p class="text-slate-500">
                            {{ $certificationAttributed->federation->location }}
                        </p>
                    </div>
                @endif



                @if($certificationAttributed->federation->country)
                    <div>
                        <div class="text-secondary font-semibold">{{ __('Country')}}</div>
                        <p class="text-slate-500 flex items-center">
                            <img
                                src="{{ asset('img/flags/' . strtolower($certificationAttributed->federation->country->iso) . '.svg') }}"
                                alt="flag" class="w-4 h-4 mr-1" />
                            <span>{{ $certificationAttributed->federation->country->name }}</span>
                        </p>
                    </div>
                @endif

                @if($certificationAttributed->federation->phone)
                    <div>
                        <div class="text-secondary font-semibold">{{ __('Phone')}}</div>
                        <p class="text-slate-500">
                            <span>{{ $certificationAttributed->federation->phone }}</span>
                        </p>
                    </div>
                @endif

                @if($certificationAttributed->federation->email)
                    <div>
                        <div class="text-secondary font-semibold">{{ __('Email')}}</div>
                        <p class="text-slate-500">
                            <span>{{ $certificationAttributed->federation->email }}</span>
                        </p>
                    </div>
                @endif


            </div>
        </div>
    </div>

@endif
