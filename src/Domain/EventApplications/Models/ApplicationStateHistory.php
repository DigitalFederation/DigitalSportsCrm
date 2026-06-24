<?php

namespace Domain\EventApplications\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $to_state
 */
class ApplicationStateHistory extends Model
{
    use HasFactory;

    protected $table = 'application_state_history';

    const UPDATED_AT = null;

    protected $fillable = [
        'application_id',
        'from_state',
        'to_state',
        'changed_by',
        'notes',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(EventApplication::class, 'application_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function stateName(): string
    {
        return __('event_applications.states.' . class_basename($this->to_state));
    }
}
