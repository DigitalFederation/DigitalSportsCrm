<?php

namespace Domain\Geographic\Models;

use App\Models\User;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Zone extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function districts(): BelongsToMany
    {
        return $this->belongsToMany(District::class, 'district_zone')
            ->withTimestamps();
    }

    public function entities(): BelongsToMany
    {
        return $this->belongsToMany(Entity::class, 'entity_zone')
            ->withTimestamps();
    }

    public function federations(): BelongsToMany
    {
        return $this->belongsToMany(Federation::class, 'federation_zone')
            ->withTimestamps();
    }

    public function individuals(): BelongsToMany
    {
        return $this->belongsToMany(Individual::class, 'individual_zone')
            ->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeWithDistrict(Builder $query, int $districtId): Builder
    {
        return $query->whereHas('districts', function ($q) use ($districtId) {
            $q->where('districts.id', $districtId);
        });
    }

    public function scopeByCountry(Builder $query, int $countryId): Builder
    {
        return $query->whereHas('districts.country', function ($q) use ($countryId) {
            $q->where('country.id', $countryId);
        });
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'LIKE', "%{$search}%")
                ->orWhere('code', 'LIKE', "%{$search}%")
                ->orWhere('description', 'LIKE', "%{$search}%");
        });
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->code ? "{$this->name} ({$this->code})" : $this->name;
    }

    public function getDistrictCountAttribute(): int
    {
        return $this->districts()->count();
    }

    public function getEntityCountAttribute(): int
    {
        return $this->entities()->count();
    }

    public function getFederationCountAttribute(): int
    {
        return $this->federations()->count();
    }

    public function getIndividualCountAttribute(): int
    {
        return $this->individuals()->count();
    }

    public function hasDistricts(): bool
    {
        return $this->districts()->exists();
    }

    public function hasAssociations(): bool
    {
        return $this->entities()->exists() ||
               $this->federations()->exists() ||
               $this->individuals()->exists();
    }
}
