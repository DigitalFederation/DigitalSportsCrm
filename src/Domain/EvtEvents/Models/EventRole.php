<?php

namespace Domain\EvtEvents\Models;

use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRole extends Model
{
    protected $table = 'evt_event_roles';

    protected $fillable = [
        'event_id',
        'individual_id',
        'role',
        'notes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    const ROLE_TECHNICAL_DELEGATE = 'technical_delegate';
    const ROLE_CHIEF_JUDGE = 'chief_judge';
    const ROLE_COMPETITION_DIRECTOR = 'competition_director';

    public static array $roles = [
        self::ROLE_TECHNICAL_DELEGATE => 'Technical Delegate',
        self::ROLE_CHIEF_JUDGE => 'Chief Judge',
        self::ROLE_COMPETITION_DIRECTOR => 'Competition Director',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function individual(): BelongsTo
    {
        return $this->belongsTo(Individual::class, 'individual_id');
    }

    public function getRoleNameAttribute(): string
    {
        return self::$roles[$this->role] ?? $this->role;
    }

    public function isTechnicalDelegate(): bool
    {
        return $this->role === self::ROLE_TECHNICAL_DELEGATE;
    }

    public function isChiefJudge(): bool
    {
        return $this->role === self::ROLE_CHIEF_JUDGE;
    }

    public function isCompetitionDirector(): bool
    {
        return $this->role === self::ROLE_COMPETITION_DIRECTOR;
    }
}
