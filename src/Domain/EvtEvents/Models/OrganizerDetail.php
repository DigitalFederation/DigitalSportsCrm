<?php

namespace Domain\EvtEvents\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizerDetail extends Model
{
    protected $table = 'evt_event_organizer_details';

    // Fillable
    protected $fillable = [
        'responsible_person',
        'email_contact',
        'phone_contact',
        'event_id',
        'bod_meeting_no',
        'date_sending_contract',
        'date_sending_invoice_loc',
        'date_reception_payment_loc',
        'date_reception_contract_signed',
        'date_reception_specific_rules',
    ];

    // cast dates
    protected $casts = [
        'date_sending_contract' => 'date',
        'date_sending_invoice_loc' => 'date',
        'date_reception_payment_loc' => 'date',
        'date_reception_contract_signed' => 'date',
        'date_reception_specific_rules' => 'date',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

}
