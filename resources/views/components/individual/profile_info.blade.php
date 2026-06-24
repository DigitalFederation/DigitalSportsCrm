<section>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-y-4 sm:gap-x-6">
        <div>
            <p class="text-secondary font-semibold">{{ __('main.Member Code') }}</p>
            <p class="text-slate-500">{{ $individual->member_code }}</p>
        </div>
        <div>
            <p class="text-secondary font-semibold">{{ __('main.full_name') }}</p>
            <p class="text-slate-500">{{ $individual->native_name ?? $individual->full_name }}</p>
        </div>
        <div>
            <p class="text-secondary font-semibold">{{ __('Name') }}</p>
            <p class="text-slate-500">{{ $individual->name }}</p>
        </div>
        <div>
            <p class="text-secondary font-semibold">{{ __('main.surname') }}</p>
            <p class="text-slate-500">{{ $individual->surname }}</p>
        </div>

        <div>
            <p class="text-secondary font-semibold">{{ __('main.Nationality') }}</p>
            <p class="text-slate-500 flex items-center"><img src="{{ asset('img/flags/' . strtolower($individual->country->iso) . '.svg') }}" alt="flag" class="w-4 h-4 mr-1"/>
            <span>{{ $individual->country->name }}</span></p>
        </div>

        <div>
            <p class="text-secondary font-semibold">{{ __('main.birthdate') }}</p>
            <p class="text-slate-500">{{ \Carbon\Carbon::parse($individual->birthdate)->format('d-m-Y') }}</p>
        </div>

        <div>
            <p class="text-secondary font-semibold">{{ __('main.gender') }}</p>
            <p class="text-slate-500">
                @if($individual->gender === 'male')
                    {{ __('main.male') }}
                @elseif($individual->gender === 'female')
                    {{ __('main.female') }}
                @else
                    -
                @endif
            </p>
        </div>

        <div class="min-w-0">
            <p class="text-secondary font-semibold">{{ __('main.email') }}</p>
            <p class="text-slate-500 break-all">{{ $individual->email }}</p>
        </div>

        @if($individual->phone)
        <div>
            <p class="text-secondary font-semibold">{{ __('main.phone') }}</p>
            <p class="text-slate-500">{{ $individual->phone }}</p>
        </div>
        @endif

        <div>
            <p class="text-secondary font-semibold">{{ __('main.identification_document') }}</p>
            <div class="text-slate-500">{{ ucwords(str_replace('_', ' ', $individual->doc_ref_type)) }}</div>
            <div class="text-slate-500 text-xs"> {{ $individual->doc_ref }} @if($individual->doc_ref_validation_date) ({{ __('main.valid_until') }}: {{ \Carbon\Carbon::parse($individual->doc_ref_validation_date)->format('d-m-Y') }}) @endif</div>
        </div>

        <div class="min-w-0">
            <p class="text-secondary font-semibold">{{ __('main.address') }}</p>
            <p class="text-slate-500 break-words">{{ $individual->address }}</p>
        </div>

        <div>
            <p class="text-secondary font-semibold">{{ __('main.location') }}</p>
            <p class="text-slate-500">{{ $individual->location }} / {{ $individual->postal_code }}</p>
        </div>

        @if($individual->district)
        <div>
            <p class="text-secondary font-semibold">{{ __('main.district') }}</p>
            <p class="text-slate-500">{{ $individual->district->name }}</p>
        </div>
        @endif

        @if($individual->vat_number)
        <div>
            <p class="text-secondary font-semibold">{{ __('main.NIF') }}</p>
            <p class="text-slate-500">{{ $individual->vat_number }}</p>
        </div>
        @endif

    </div>
</section>
