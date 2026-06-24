<?php

namespace Domain\EvtEvents\Models;

use Domain\Federations\Models\Federation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TechnicalDelegate extends Model
{
    use HasFactory;

    protected $table = 'evt_technical_delegates';

    protected $fillable = [
        'competition_id',
        'name',
        'federation_id',
        'individual_id',
        'member_code_delegate_federation',
        'appointment_by_bod_number',
        'date_of_bod_appointment',
        'date_of_report_reception',
        'remarks',
        'date_bod_validation_report',
        'num_bod_validation_report',
    ];

    protected $casts = [
        'date_of_bod_appointment' => 'datetime',
        'date_bod_validation_report' => 'datetime',
        'date_of_report_reception' => 'datetime',
    ];

    /**
     * Get the federation associated with the technical delegate.
     */
    public function federation(): BelongsTo
    {
        return $this->belongsTo(Federation::class);
    }

    /**
     * Get the competition associated with the technical delegate.
     */
    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }
}
