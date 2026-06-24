<?php

namespace Domain\EventApplications\Models;

use Domain\EvtEvents\Models\Sport;
use Domain\Geographic\Models\District;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property array<string, mixed>|null $form_data
 * @property class-string<\Domain\EventApplications\States\ApplicationState>|null $status_class
 * @property int|null $id
 * @property int|string|null $entity_id
 * @property string|null $entity_type
 * @property string|null $event_name
 * @property string|null $event_type
 * @property \Illuminate\Support\Carbon|null $start_date
 * @property \Illuminate\Support\Carbon|null $end_date
 * @property \Illuminate\Support\Carbon|null $submitted_at
 * @property string|null $responsible_name
 * @property string|null $responsible_phone
 */
class EventApplication extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $table = 'event_applications';

    protected static function newFactory(): \Database\Factories\Domain\EventApplications\EventApplicationFactory
    {
        return \Database\Factories\Domain\EventApplications\EventApplicationFactory::new();
    }

    protected $fillable = [
        'application_type',
        'template_id',
        'entity_id',
        'entity_type',
        'status_class',
        'event_name',
        'event_type',
        'sport_id',
        'event_category',
        'category',
        'start_date',
        'end_date',
        'district_id',
        'municipality',
        'responsible_name',
        'responsible_phone',
        'target_audience',
        'expected_participants',
        'form_data',
        'admin_notes',
        'submitted_at',
        'validated_at',
        'decided_at',
        'published_at',
        'published_event_id',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'submitted_at' => 'datetime',
            'validated_at' => 'datetime',
            'decided_at' => 'datetime',
            'published_at' => 'datetime',
            'expected_participants' => 'integer',
            'form_data' => 'array',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ApplicationTemplate::class, 'template_id');
    }

    public function entity(): MorphTo
    {
        return $this->morphTo('entity');
    }

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class, 'application_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ApplicationComment::class, 'application_id');
    }

    public function stateHistory(): HasMany
    {
        return $this->hasMany(ApplicationStateHistory::class, 'application_id');
    }

    public function getFormSection(string $key, mixed $default = null): mixed
    {
        return data_get($this->form_data, $key, $default);
    }

    public function setFormSection(string $key, mixed $value): void
    {
        $formData = $this->form_data ?? [];
        data_set($formData, $key, $value);
        $this->form_data = $formData;
    }

    public function scopeHasApplied(Builder $query, int $entityId, int $templateId): bool
    {
        return $query->where('entity_id', $entityId)
            ->where('template_id', $templateId)
            ->whereNull('deleted_at')
            ->exists();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('application-attachments');
    }

    public function stateName(): string
    {
        return __('event_applications.states.' . $this->state->name());
    }

    public function stateColor(): string
    {
        return $this->state->color();
    }

    public function getStateAttribute()
    {
        $statusClass = $this->status_class ?? \Domain\EventApplications\States\DraftApplicationState::class;

        if (empty($statusClass) || ! class_exists($statusClass)) {
            $statusClass = \Domain\EventApplications\States\DraftApplicationState::class;
        }

        return new $statusClass($this);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($application) {
            if (empty($application->status_class)) {
                $application->status_class = \Domain\EventApplications\States\DraftApplicationState::class;
            }
        });
    }
}
