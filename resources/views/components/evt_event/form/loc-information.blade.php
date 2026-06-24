<section class="card">

    <div class="flex flex-row gap-x-2 items-center border-b border-gray-300 pb-2 mb-4">
        <x-svg.info class="w-6 h-6 text-slate-600" />
        <div class="font-bold">{{ __('LOC Details') }}</div>
    </div>


    <div class="flex flex-col md:flex-row gap-4 w-full mb-4">
        <!-- BoD Meeting Number -->
        <div class="w-full md:w-1/3">
            <label class="block text-sm font-medium mb-1" for="organizer_details_responsible_person">
                {{ __('Responsible Person') }}
            </label>
            <input type="text"
                   name="organizer_details[responsible_person]"
                   id="organizer_details_responsible_person"
                   value="{{ old('organizer_details.responsible_person', $event->organizerDetails->responsible_person ?? '') }}"
                   class="form-input w-full">
            <p class="text-xs text-gray-400">{{ __('The name of the person responsible.') }}</p>
            @if($errors->has('organizer_details.responsible_person'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('organizer_details.responsible_person') }}
                </div>
            @endif
        </div>

        <div class="w-full md:w-1/3">
            <label class="block text-sm font-medium mb-1" for="organizer_details_contact_email">
                {{ __('Contact Email') }}
            </label>
            <input type="text"
                   name="organizer_details[email_contact]"
                   id="organizer_details_email_contact"
                   value="{{ old('organizer_details.email_contact', $event->organizerDetails->email_contact ?? '') }}"
                   class="form-input w-full">
            <p class="text-xs text-gray-400">{{ __('A valid email for contact.') }}</p>
            @if($errors->has('organizer_details.email_contact'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('organizer_details.email_contact') }}
                </div>
            @endif
        </div>
        <div class="w-full md:w-1/3">
            <label class="block text-sm font-medium mb-1" for="organizer_details_phone_contact">
                {{ __('Contact Phone') }}
            </label>
            <input type="text"
                   name="organizer_details[phone_contact]"
                   id="organizer_details_phone_contact"
                   value="{{ old('organizer_details.phone_contact', $event->organizerDetails->phone_contact ?? '') }}"
                   class="form-input w-full">
            <p class="text-xs text-gray-400">{{ __('Phone number of responsible person') }}</p>
            @if($errors->has('organizer_details.phone_contact'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('organizer_details.phone_contact') }}
                </div>
            @endif
        </div>
    </div>


    <div class="flex flex-col md:flex-row gap-4 w-full">
        <!-- BoD Meeting Number -->
        <div class="w-full">
            <label class="block text-sm font-medium mb-1" for="organizer_details_bod_meeting_no">
                {{ __('BoD Meeting Nº') }}
            </label>
            <input type="text"
                   name="organizer_details[bod_meeting_no]"
                   id="organizer_details_bod_meeting_no"
                   value="{{ old('organizer_details.bod_meeting_no', $event->organizerDetails->bod_meeting_no ?? '') }}"
                   class="form-input w-full">
            <p class="text-xs text-gray-400">{{ __('The board meeting number related to the event.') }}</p>
            @if($errors->has('organizer_details.bod_meeting_no'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('organizer_details.bod_meeting_no') }}
                </div>
            @endif
        </div>
    </div>
    <div class="mt-4 flex flex-col md:flex-row gap-4 w-full">
        <!-- Date Sending Contract -->
        <div class="w-full md:w-1/2">
            <label class="block text-sm font-medium mb-1" for="organizer_details_date_sending_contract">
                {{ __('Date Sending Contract') }}
            </label>
            <input type="date"
                   name="organizer_details[date_sending_contract]"
                   value="{{ old('organizer_details.date_sending_contract', $event->organizerDetails->date_sending_contract ?? '') }}"
                   id="organizer_details_date_sending_contract"
                   class="form-input w-full">
            @if($errors->has('organizer_details.date_sending_contract'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('organizer_details.date_sending_contract') }}
                </div>
            @endif
        </div>
        <!-- Date Sending Invoice LOC -->
        <div class="w-full md:w-1/2">
            <label class="block text-sm font-medium mb-1" for="organizer_details_date_sending_invoice">
                {{ __('Date Sending Invoice') }}
            </label>
            <input type="date"
                   name="organizer_details[date_sending_invoice]"
                   value="{{ old('organizer_details.date_sending_invoice', $event->organizerDetails->date_sending_invoice ?? '') }}"
                   id="organizer_details_date_sending_invoice"
                   class="form-input w-full">
            @if($errors->has('organizer_details.date_sending_invoice'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('organizer_details.date_sending_invoice') }}
                </div>
            @endif
        </div>
    </div>
    <div class="mt-4 flex flex-col md:flex-row gap-4 w-full">
        <!-- Date Reception Payment LOC -->
        <div class="w-full md:w-1/2">
            <label class="block text-sm font-medium mb-1" for="organizer_details_date_reception_payment">
                {{ __('Reception Payment LOC') }}
            </label>
            <input type="date"
                   name="organizer_details[date_reception_payment]"
                   value="{{ old('organizer_details.date_reception_payment', $event->organizerDetails->date_reception_payment ?? '') }}"
                   id="organizer_details_date_reception_payment"
                   class="form-input w-full">
            @if($errors->has('organizer_details.date_reception_payment'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('organizer_details.date_reception_payment') }}
                </div>
            @endif
        </div>
        <!-- Date Reception Contract Signed -->
        <div class="w-full md:w-1/2">
            <label class="block text-sm font-medium mb-1" for="organizer_details_date_reception_contract_signed">
                {{ __('Recep. Contract Signed') }}
            </label>
            <input type="date"
                   name="organizer_details[date_reception_contract_signed]"
                   value="{{ old('organizer_details.date_reception_contract_signed', $event->organizerDetails->date_reception_contract_signed ?? '') }}"
                   id="organizer_details_date_reception_contract_signed"
                   class="form-input w-full">
            @if($errors->has('organizer_details.date_reception_contract_signed'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('organizer_details.date_reception_contract_signed') }}
                </div>
            @endif
        </div>
    </div>
    <div class="mt-4 flex flex-col md:flex-row gap-4 w-full">
        <!-- Date Reception Specific Rules -->
        <div class="w-full md:w-1/2">
            <label class="block text-sm font-medium mb-1" for="organizer_details_date_reception_specific_rules">
                {{ __('Reception Specific Rules') }}
            </label>
            <input type="date"
                   name="organizer_details[date_reception_specific_rules]"
                   value="{{ old('organizer_details.date_reception_specific_rules', $event->organizerDetails->date_reception_specific_rules ?? '') }}"
                   id="organizer_details_date_reception_specific_rules"
                   class="form-input w-full">
            @if($errors->has('organizer_details.date_reception_specific_rules'))
                <div class="text-xs mt-1 text-rose-500 h-2">
                    {{ $errors->first('organizer_details.date_reception_specific_rules') }}
                </div>
            @endif
        </div>
    </div>

</section>
