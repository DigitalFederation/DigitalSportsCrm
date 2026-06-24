<div class="card w-full overflow-hidden">

    <div class="absolute right-0 top-0 h-16 w-16">
        <div
            class="absolute transform rotate-45 bg-{{ Str::slug($certification->stateName()) }} text-center text-white font-semibold py-1 right-[-40px] top-[30px] w-[170px]">
            {{ ucfirst($certification->stateName()) }}
        </div>
    </div>

    <!-- Card content -->
    <div class="flex flex-col md:flex-row gap-y-2 justify-between">

        <div class="md:w-1/2 flex flex-col gap-y-4">
            <div>
                <div class="text-secondary font-semibold">{{ __('Certification')}}</div>
                <p class="text-slate-500">
                    {{ empty($certification->name)? $certification->certification->name : $certification->name }}
                </p>
            </div>


            <div>
                <div class="text-secondary font-semibold">{{ __('Student')}}</div>
                <p class="text-slate-500 hover:text-slate-700 hover:underline">
                    {{ $certification->holder_name }}
                    @if(!empty($certification->individual))
                        ({{ $certification->individual?->member_code }})
                    @endif
                </p>
            </div>


            <div>
                <div class="text-secondary font-semibold">{{ __('International Number')}}</div>
                <p class="text-slate-500">
                    {{ !empty($certification->license_number) ? $certification->license_number : __('Not defined') }}
                </p>
            </div>

            @if(empty($certification->mainInstructor->first()) && $certification->certification->committee_id != 1)
                <div>
                    <div class="text-secondary font-semibold">{{ __('Course Director')}}</div>
                    <p class="text-slate-500">National Technical Committee</p>
                </div>
            @endif
        </div>

        <div class="md:w-1/2 flex flex-col gap-y-4">

            @if(!empty($certification->national_code))
                <div>
                    <div class="text-secondary font-semibold">{{ __('National Certification Number') }}</div>
                    <p class="text-slate-500">{{ $certification->national_code }}</p>
                </div>
            @endif


            <div>
                <div class="text-secondary font-semibold">{{ __('Issue Date') }}</div>
                <p class="text-slate-500">
                    {{ $certification->current_term_starts_at ? date('d-m-Y', strtotime($certification->current_term_starts_at)) : '---' }}
                </p>
            </div>

            <div>
                <div class="text-secondary font-semibold">{{ __('Expire Date')}}</div>
                <p class="text-slate-500">
                    @if(empty($certification->current_term_ends_at))
                        <span
                            class="p-1 px-2 bg-slate-400 text-white rounded-xl text-xs">{{ __('No expiration date') }}</span>
                    @else
                        {{ date('d/m/Y', strtotime($certification->current_term_ends_at)) }}
                    @endif
                </p>
            </div>

            @if($certification->activator)
                <div>
                    <div class="text-secondary font-semibold">{{ __('Approved by')}}</div>
                    <p class="text-slate-500">{{ $certification->activator->name }}</p>
                </div>
            @endif



            @if($certification->activated_at)
                <div>
                    <div class="text-secondary font-semibold">{{ __('Approved date')}}</div>
                    <p class="text-slate-500">{{ date('d/m/Y', strtotime($certification->activated_at)) }}</p>
                </div>
            @endif

        </div>

    </div>

    @php
        $isFederation = auth()->user()->isFederation();
        $is_local = $isFederation && auth()->user()->federations()->first()->is_local;
        $isAdmin = auth()->user()->group()->first()->code == 'ADMIN';
        $isInstructor = !empty(auth()->user()->individuals()->first()) && auth()->user()->individuals()->first()->id === $certification->instructor_id;

        $isActiveCertification = $certification->isActive() && $certification->status_class == \Domain\Certifications\States\ActiveCertificationAttributedState::class;
        $isSuspendedCertification = !$certification->isActive() && $certification->status_class == \Domain\Certifications\States\SuspendedCertificationAttributedState::class;
        $isPendingCertification = !$certification->isActive() &&
        ( $certification->stateName() == 'pending' ||
        $certification->stateName() == 'provisional' ||
        $certification->status_class == \Domain\Certifications\States\DirectorApprovedCertificationAttributedState::class);

       use Carbon\Carbon;

        $isExpired = false;
        if (!empty($certification->current_term_ends_at)) {
            $expirationDate = Carbon::parse($certification->current_term_ends_at);
            $isExpired = $expirationDate->isPast();
        }

        $canUnsuspend = $isSuspendedCertification && !$isExpired;
    @endphp

    @if($isInstructor && $certification->status_class == \Domain\Certifications\States\DirectorApprovalCertificationAttributedState::class)
        <div class="md:flex md:flex-row flex-col md:gap-x-4">

            <form action="{{ route('individual.certification-attributed.activate') }}" method="post">
                @csrf
                <input type="hidden" name="id" value="{{ $certification->id }}">
                <input type="hidden" name="quantity" value="1">
                <div class="md:flex md:flex-row justify-end pt-8 md:gap-x-4">
                    <button type="submit" class="btn btn-primary">{{ __('Approve Request')}}</button>
                </div>
            </form>

            <form action="{{ route('individual.certification-attributed.cancel') }}" method="post">
                @csrf
                <input type="hidden" name="id" value="{{ $certification->id }}">
                <input type="hidden" name="quantity" value="1">
                <div class="md:flex md:flex-row justify-end pt-8 md:gap-x-4 w-full">
                    <button type="submit" class="btn btn-danger">{{ __('Reject Request')}}</button>
                </div>
            </form>

        </div>
    @endif

    @if($isPendingCertification && ($isFederation || $isAdmin))

        <div class="md:flex  flex-col gap-y-4  md:gap-4 mt-4 justify-between">

            @if(($isFederation && !$is_local) || $isAdmin)

                @if(!empty($certification->national_code))
                    <form action="{{ route(Request::segment(1).'.certification-attributed.activate') }}" method="post">
                        @csrf
                        <input type="hidden" name="id" value="{{ $certification->id }}">
                        <input type="hidden" name="quantity" value="1">

                        <button id="validateButton" type="submit"
                                class="btn-primary w-full">{{ __('Validate Certification')}}</button>

                    </form>
                @endif

                <form
                    class="w-full"
                    action="{{ route(Request::segment(1).'.certification-attributed.cancel') }}"
                    method="post"
                    onsubmit="return confirm('{{ __('Are you sure you want to Reject this Certification?') }}')"
                >
                    @csrf
                    <input type="hidden" name="id" value="{{ $certification->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <div class="md:flex md:flex-row justify-end md:gap-x-4 mt-2 md:mt-0 w-full">
                        <button type="submit"
                                class="btn btn-danger w-full">{{ __('Reject Certification')}}</button>
                    </div>
                </form>

                <div @details-updated.window="location.reload()" class="mt-2 md:mt-0 w-full">
                    <x-dynamic-modal
                        :viewName="'edit-certification-details'"
                        :params="['certification' => $certification]"
                        headerTitle="Edit Certification Details"
                        buttonLabel="Validation and Details"
                        buttonClass="btn btn-success w-full cursor-pointer "
                        :isLivewire="true"
                        animation="transition ease-in duration-200"
                    />
                </div>

            @endif

        </div>

    @endif

    @if($isActiveCertification && ($isFederation || $isAdmin))
        <form
            action="{{ route(Request::segment(1).'.certification-attributed.suspend') }}"
            method="post"
            onsubmit="return confirm('{{ __('Are you sure you want to Suspend this Certification?') }}')">
            @csrf
            <input type="hidden" name="id" value="{{ $certification->id }}">
            <div class="md:flex md:flex-row justify-end pt-8 md:gap-x-4 w-full">
                <button type="submit" class="btn btn-danger w-full md:w-auto">{{ __('Suspend Certification')}}</button>
            </div>
        </form>
    @endif

    @if($canUnsuspend && ($isFederation || $isAdmin))
        <form
            action="{{ route(Request::segment(1).'.certification-attributed.unsuspend') }}"
            method="post"
            onsubmit="return confirm('{{ __('Are you sure you want to Activate this Certification?') }}')">
            @csrf
            <input type="hidden" name="id" value="{{ $certification->id }}">
            <div class="md:flex md:flex-row justify-end pt-8 md:gap-x-4">
                <button type="submit" class="btn btn-primary">{{ __('Activate Certification')}}</button>
            </div>
        </form>
    @endif

</div>

<div class="flex justify-between flex-col items-start gap-x-4">

    <x-certification_attributed.organization_card :certificationAttributed="$certification" />

    <x-certification_attributed.entity_card :certificationAttributed="$certification" />

</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        window.addEventListener("detailsUpdated", function() {
            // Enable the validation button if national code is present
            let validateButton = document.getElementById("validateButton");
            validateButton.disabled = false;
        });
    });
</script>
