<?php

namespace Domain\EvtEvents\Models;

use Database\Factories\EvtSportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @method static select(string ...$columns)
 */
class Sport extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $table = 'evt_sports';

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

    /**
     * Get the translated name for the sport.
     */
    public function getTranslatedNameAttribute(): string
    {
        $key = 'sports.' . str_replace(' ', '_', strtolower($this->name));

        $translated = __($key);

        return $translated !== $key ? $translated : $this->name;
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

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('hero-image')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    protected static function newFactory(): EvtSportFactory
    {
        return EvtSportFactory::new();
    }

    public function disciplines()
    {
        return $this->hasMany(Discipline::class, 'sport_id');
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'sport_id');
    }
}
