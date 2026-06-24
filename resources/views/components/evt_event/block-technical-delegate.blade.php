@if($event->isSportEvent())
    <div class="card w-full md:w-1/3 h-auto" x-data="{ open: true }">
        <div class="flex gap-x-2  items-center border-b border-gray-300 pb-2 mb-4">
            <div class="flex gap-x-2 items-center">
                <x-svg.person-lines class="w-6 h-6 text-slate-600" />
                <span class="font-bold">{{ __('Technical Delegate') }}</span>
            </div>


        </div>


        <div class="flex flex-col gap-2 items-baseline" x-show="open">
            <!-- Info Technical Delegate -->
            @if(isset($event->competitions->first()->technicalDelegates) && !empty($event->competitions->first()->technicalDelegates->first()))
                <div>
                    <p class="text-xs text-slate-400">Name</p>
                    <p class="text-sm text-slate-600">{{ $event->competitions->first()->technicalDelegates->first()->name }}</p>
                </div>

                @if($event->competitions->first()->technicalDelegates->first()->federation)
                    <div>
                        <p class="text-xs text-slate-400">Federation</p>
                        <p class="text-sm text-slate-600">{{ $event->competitions->first()->technicalDelegates->first()->federation?->member_code }}</p>
                    </div>
                @endif

                <div>
                    <p class="text-xs text-slate-400">{{ __('certifications.member_code') }}</p>
                    <p class="text-sm text-slate-600">{{ $event->competitions->first()->technicalDelegates->first()->member_code_delegate_federation }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">Appointment by BOD Nº</p>
                    <p class="text-sm text-slate-600">{{ $event->competitions->first()->technicalDelegates->first()->appointment_by_bod_number }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">Date of BOD Appointment</p>
                    <p class="text-sm text-slate-600">{{ \Carbon\Carbon::parse($event->competitions->first()->technicalDelegates->first()->date_of_bod_appointment)->format('d/m/Y') }}</p>
                </div>
            @else
                <x-utility.no-data :inCard="true" />
            @endif
        </div>
    </div>
@endif
