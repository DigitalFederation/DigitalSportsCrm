<?php

namespace App\Models;

use Database\Factories\SportFactory;
use Domain\Certifications\Models\Certification;
use Domain\Entities\Models\EntityAthlete;
use Domain\EvtEvents\Models\Discipline;
use Domain\EvtEvents\Models\Event;
use Domain\Licenses\Models\License;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @method static create(array[] $sports)
 * @method static select(string ...$columns)
 */
class Sport extends Model
{
    use HasFactory;

    protected $table = 'sports';

    protected $fillable = [
        'name',
        'sport_type',
    ];

    public function getSportTypeLabelAttribute(): string
    {
        return match ($this->sport_type) {
            'individual' => __('sports.individual'),
            'team' => __('sports.team'),
            default => '',
        };
    }

    protected static function newFactory(): SportFactory
    {
        return SportFactory::new();
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(License::class);
    }

    public function licensesViaPivot(): BelongsToMany
    {
        return $this->belongsToMany(License::class, 'license_sport')
            ->withTimestamps();
    }

    public function certifications(): BelongsToMany
    {
        return $this->belongsToMany(Certification::class, 'certification_sport');
    }

    public function entityAthletes(): HasMany
    {
        return $this->hasMany(EntityAthlete::class);
    }

    public function disciplines()
    {
        return $this->hasMany(Discipline::class, 'sport_id');
    }
    public function events()
    {
        return $this->hasMany(Event::class, 'sport_id');
    }
    /**
     * Get the URL of the predefined image for the sport.
     *
     * @return string|null The URL of the image or null if no image is defined.
     */
    public function getPredefinedImageUrl(): ?string
    {
        $mapping = [
            'Swimming' => asset('images/predefined/swimming.jpg'),
            'Diving' => asset('images/predefined/diving.jpg'),
        ];

        return $mapping[$this->name] ?? null; // Return the image URL or null if not found
    }

    /**
     * Get the translated sport name based on current locale.
     */
    public function getTranslatedNameAttribute(): string
    {
        $key = 'sports.' . Str::slug($this->name, '_');
        $translated = __($key);

        // Fallback to original name if translation doesn't exist
        return $translated === $key ? $this->name : $translated;
    }

    /**
     * Get the 2-letter abbreviation for the sport.
     */
    public function getTranslatedAbbreviationAttribute(): string
    {
        $key = 'sports.' . Str::slug($this->name, '_') . '_abbr';
        $translated = __($key);

        return $translated === $key ? mb_strtoupper(mb_substr($this->translated_name, 0, 2)) : $translated;
    }
}
