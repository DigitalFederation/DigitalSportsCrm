@if(!empty(session('success')))
    <div class="flex gap-4 bg-gradient-to-r from-emerald-500 to-green-500 p-4 rounded-xl mb-6 items-center shadow-md">
        <div class="w-max">
            <div class="flex rounded-full bg-white/20 p-2 text-white">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                     stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
        <div>
            <h6 class="font-semibold text-white text-base">{{ __('Success') }}</h6>
            <p class="text-white/90 leading-tight text-sm mt-1">{!! session('success') !!}</p>
        </div>
    </div>
@endif

@if(!empty(session('information')))
    <div class="flex gap-4 bg-white p-4 rounded-xl mb-6 items-start shadow-md border-l-4 border-blue-500">
        <div class="flex rounded-full bg-blue-100 p-2 text-blue-600 flex-shrink-0">
            <x-svg.ticket class="w-5 h-5"></x-svg.ticket>
        </div>

        <div>
            <h6 class="font-semibold text-slate-800 mb-1 text-base">{{ __('Information') }}</h6>
            <p class="text-slate-600 leading-tight text-sm">{!! session('information') !!}</p>
        </div>
    </div>
@endif
@if(!empty(session('message')))
    <div class="flex gap-4 bg-white p-4 rounded-xl mb-6 items-start shadow-md border-l-4 border-blue-500">
        <div class="flex rounded-full bg-blue-100 p-2 text-blue-600 flex-shrink-0">
            <x-svg.info class="w-5 h-5"></x-svg.info>
        </div>

        <div>
            <h6 class="font-semibold text-slate-800 mb-1 text-base">{{ __('Information') }}</h6>
            <p class="text-slate-600 leading-tight text-sm">{!! session('message') !!}</p>
        </div>
    </div>
@endif
@if(session()->has('error') || session()->has('errors') || $errors->any())
    <div class="flex gap-4 bg-gradient-to-r from-red-500 to-rose-500 p-4 rounded-xl mb-6 items-center shadow-md">
        <div class="w-max">
            <div class="flex rounded-full bg-white/20 p-2 text-white">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                     stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
            </div>
        </div>
        <div>
            <h6 class="font-semibold text-white text-base">{{ __('Attention') }}</h6>
            @if(!empty(session('errors')))
                <ul class="text-white/90 leading-tight list-disc list-inside mt-1 text-sm">
                    @foreach(session('errors')->getMessages() as $err)
                        @foreach($err as $er)
                            <li>{{ $er }}</li>
                        @endforeach
                    @endforeach
                </ul>
            @endif
            <p class="text-white/90 leading-tight text-sm mt-1">{{ session('error') }}</p>
        </div>
    </div>
@endif
