<div class="card w-full overflow-hidden">
    <div class="absolute right-0 top-0 h-16 w-16">
        <div
            class="absolute transform rotate-45 bg-{{ $certification->stateName() }} text-center text-white font-semibold py-1 right-[-40px] top-[30px] w-[170px]">
            {{ ucfirst($certification->stateName()) }}
        </div>
    </div>

    <!-- Card content -->
    <div class="flex flex-col flex-auto mt-6">

        <div class="mb-2">
            <div class="text-secondary font-semibold">{{ __('International Number')}}</div>
            <p class="text-slate-500">
                {{ !empty($certification->license_number) ? $certification->license_number : __('Not defined') }}
            </p>
        </div>


        <div class="mb-2">
            <div class="text-secondary font-semibold">{{ __('Certification')}}</div>
            <p class="text-slate-500">
                {{ empty($certification->name)? $certification->certification->name : $certification->name }}
            </p>
        </div>

        <div class="mb-2">
            <div class="text-secondary font-semibold">{{ __('Valid Until')}}</div>
            <p class="text-slate-500">{{ date('d/m/Y', strtotime($certification->current_term_ends_at)) }}</p>
        </div>
        <div class="mb-2">
            <div class="text-secondary font-semibold">{{ __('Federation name')}}</div>
            <p class="text-slate-500">{{ $certification->federation->name }}</p>
        </div>
        <div class="mb-2">
            <div class="text-secondary font-semibold">{{ __('National license number')}}</div>
            <p class="text-slate-500">{{ $certification->national_license_code }}</p>
        </div>
        <div class="mb-2">
            <div class="text-secondary font-semibold">{{ __('International license number')}}</div>
            <p class="text-slate-500">{{ $certification->cmas_license_code }}</p>
        </div>
        <div class="mb-2">
            <div class="text-secondary font-semibold">{{ __('Request date')}}</div>
            <p class="text-slate-500">{{ date('d/m/Y', strtotime($certification->created_at)) }}</p>
        </div>

        @if($certification->activator)
            <div class="mb-2">
                <div class="text-secondary font-semibold">{{ __('Approved by')}}</div>
                <p class="text-slate-500">{{ $certification->activator->name }}</p>
            </div>
        @endif

        @if($certification->activated_at)
            <div class="mb-2">
                <div class="text-secondary font-semibold">{{ __('Approved date')}}</div>
                <p class="text-slate-500">{{ date('d/m/Y', strtotime($certification->activated_at)) }}</p>
            </div>
        @endif
    </div>


    @if(!$certification->isActive() &&
        $certification->stateName() == 'pending' &&
        (in_array(auth()->user()->group()->first()->code, ['ADMIN', 'FEDERATION'])) ||
        !empty(auth()->user()->individuals()->first()) &&
        auth()->user()->individuals()->first()->id === $certification->instructor_id)

        <div class="md:flex flex-auto items-end justify-end md:gap-x-4">
            <a href="{{ URL::previous() }}" class="btn bg-slate-500 text-white">{{ __('Back') }}</a>

            <form action="{{ route('admin.certification-attributed.activate') }}" method="post">
                @csrf
                <input type="hidden" name="id" value="{{ $certification->id }}">
                <input type="hidden" name="quantity" value="1">
                <div class="md:flex md:flex-row justify-end pt-8 md:gap-x-4">
                    <button type="submit"
                            class="bg-emerald-500 rounded py-2 px-5 font-bold text-white">{{ __('Validate Certification')}}</button>
                </div>
            </form>

        </div>
    @endif

</div>
