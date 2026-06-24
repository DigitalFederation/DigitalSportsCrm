<?php

namespace Domain\EvtEvents\Models;

use Database\Factories\DisciplineFactory;
use Domain\Licenses\Models\License;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discipline extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'evt_disciplines';

    protected $fillable = [
        'name',
        'sport_id',
        'gender',
        'enrollment_type',
        'enrollment_type_value',
        'team_composition_requirements',
        'athlete_limit',
        'distance',
        'style',
    ];

    protected $casts = [
        'team_composition_requirements' => 'array',
    ];
    public function setTeamCompositionRequirementsAttribute($value)
    {
        $this->attributes['team_composition_requirements'] = is_array($value) ? json_encode($value) : $value;
    }

    protected static function newFactory(): DisciplineFactory
    {
        return DisciplineFactory::new();
    }

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class, 'sport_id');
    }

    public function attributes(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'evt_discipline_attribute_association', 'discipline_id', 'attribute_id')
            ->withPivot('custom_value');
    }

    public function competitions(): BelongsToMany
    {
        return $this->belongsToMany(Competition::class, 'evt_competition_discipline', 'discipline_id', 'competition_id');
    }

    public function licenses(): BelongsToMany
    {
        return $this->belongsToMany(License::class, 'evt_discipline_licenses', 'discipline_id', 'license_id');
    }

    public function sportAgeGroups(): BelongsToMany
    {
        return $this->belongsToMany(SportAgeGroup::class, 'evt_discipline_sport_age_groups', 'discipline_id', 'sport_age_group_id');
    }

    public function scopeGlobalAttributes($query)
    {
        return $this->attributes()->where('fillable_global', true);
    }

    public function scopeSpecificAttributes($query)
    {
        return $this->attributes()->where('fillable_global', false);
    }

    public function scopeFilterName($query, $value)
    {
        return $query->where('name', 'like', "%{$value}%");
    }

    public function scopeFilterSport($query, $value)
    {
        return $query->where('sport_id', $value);
    }

    public function scopeFilterEnrollmentType($query, $value)
    {
        return $query->where('enrollment_type', $value);
    }
    public function getFormattedTeamCompositionAttribute(): string
    {
        if (! $this->team_composition_requirements) {
            return 'No team composition requirements';
        }

        return collect($this->team_composition_requirements)
            ->map(function ($count, $gender) {
                return "{$count} {$gender} player" . ($count !== 1 ? 's' : '');
            })
            ->join(', ');
    }

    public function getOutOfRaceAttributeId(): ?int
    {
        return $this->attributes()
            ->where('evt_attributes.attribute_type', 'OUTOFRACE')
            ->value('evt_attributes.id');
    }
}
