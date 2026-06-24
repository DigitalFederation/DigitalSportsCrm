<?php

namespace Domain\EvtEvents\Models;

use App\Enums\EvtCompetitionTypeEnum;
use App\Models\Country;
use Database\Factories\CompetitionFactory;
use Domain\Certifications\Models\Certification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Mpociot\Versionable\VersionableTrait;

/**
 * @property array<int, int>|null $required_athlete_licenses
 * @property \Illuminate\Database\Eloquent\Collection<int, \Domain\Licenses\Models\License> $requiredAthleteLicenses
 */
class Competition extends Model
{
    use HasFactory, VersionableTrait;

    protected $table = 'evt_competitions';
    protected $appends = ['types_names'];

    protected $fillable = [
        'event_id',
        'year',
        'month',
        'number',
        'sport_id',
        'rounds_total',
        'cat_age',
        'cat_competition',
        'environment', // Open Water, Swimming Pool, Depth
        'full_name',
        'status_class',
        'venue',
        'venue_address',
        'venue_city',
        'venue_country_id',
        'start_date',
        'end_date',
        'medals_gold',
        'medals_silver',
        'medals_bronze',
        'medals_type',
        'trophies_first',
        'trophies_second',
        'trophies_third',
        'discipline_template_id',
        'required_athlete_licenses',
        'required_coach_certifications',
        'required_referee_certifications',
        'requires_athlete_adel',
        'requires_coach_adel',
        'requires_referee_adel',
        'requires_official_adel',
        'requires_local_federation_affiliation',
        'requires_athlete_entity_sport_registration',
        'requires_coach_entity_sport_registration',
        'required_athlete_documents',
        'required_coach_documents',
        'required_referee_documents',
        'required_official_documents',
        'max_disciplines_per_athlete',
        'max_relays_per_athlete',
        'max_teams_per_athlete',
        'moloni_reference',
    ];

    protected $casts = [
        'start_date' => 'datetime:Y-m-d',
        'end_date' => 'datetime:Y-m-d',
        'required_athlete_licenses' => 'array',
        'required_coach_certifications' => 'array',
        'required_referee_certifications' => 'array',
        'requires_athlete_adel' => 'boolean',
        'requires_coach_adel' => 'boolean',
        'requires_referee_adel' => 'boolean',
        'requires_official_adel' => 'boolean',
        'requires_local_federation_affiliation' => 'boolean',
        'requires_athlete_entity_sport_registration' => 'boolean',
        'requires_coach_entity_sport_registration' => 'boolean',
        'required_athlete_documents' => 'array',
        'required_coach_documents' => 'array',
        'required_referee_documents' => 'array',
        'required_official_documents' => 'array',
        'max_disciplines_per_athlete' => 'integer',
        'max_relays_per_athlete' => 'integer',
        'max_teams_per_athlete' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        /**
         * Listen for changes on sport_id and update all related disciplines
         *
         * @param  Competition  $competition
         * @return void
         */
        static::updated(function ($competition) {
            // Check if sport_id is changed
            if ($competition->isDirty('sport_id')) {
                // Update sport_id in all related disciplines
                $competition->disciplines()->update(['sport_id' => $competition->sport_id]);
            }
        });
    }

    protected static function newFactory(): CompetitionFactory
    {
        return CompetitionFactory::new();
    }

    /**
     * Accessor to get readable competition type names.
     *
     * @return array
     */
    public function getTypesNamesAttribute()
    {
        return $this->types->map(function ($type) {
            return EvtCompetitionTypeEnum::toString($type->competition_type);
        })->toArray();
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    public function disciplines(): BelongsToMany
    {
        return $this->belongsToMany(Discipline::class, 'evt_competition_discipline', 'competition_id', 'discipline_id');
    }

    public function disciplineTemplate(): BelongsTo
    {
        return $this->belongsTo(DisciplineTemplate::class, 'discipline_template_id');
    }

    public function antiDopingRecord(): HasOne
    {
        return $this->hasOne(AntiDoping::class);
    }

    public function antiDopingRecords(): HasMany
    {
        return $this->hasMany(AntiDoping::class);
    }

    public function technicalDelegates(): HasMany
    {
        return $this->hasMany(TechnicalDelegate::class);
    }

    public function technical_delegates(): HasMany
    {
        return $this->technicalDelegates();
    }

    public function referees(): HasMany
    {
        return $this->hasMany(CompetitionReferee::class);
    }

    public function competitionStaff(): HasMany
    {
        return $this->hasMany(CompetitionStaff::class);
    }

    public function types(): HasMany
    {
        return $this->hasMany(CompetitionType::class);
    }

    public function requiredRefereeCertifications(): BelongsToMany
    {
        return $this->belongsToMany(Certification::class, 'evt_competition_referee_certification', 'competition_id', 'certification_id');
    }

    public function requiredCoachCertifications(): BelongsToMany
    {
        return $this->belongsToMany(Certification::class, 'evt_competition_coach_certification', 'competition_id', 'certification_id');
    }

    public function venueCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'venue_country_id');
    }

    /**
     * Get required documents for a specific enrollment type.
     */
    public function getRequiredDocumentsFor(string $enrollmentType): array
    {
        return match ($enrollmentType) {
            'athlete' => $this->required_athlete_documents ?? [],
            'coach' => $this->required_coach_documents ?? [],
            'referee' => $this->required_referee_documents ?? [],
            'official' => $this->required_official_documents ?? [],
            default => [],
        };
    }
}
