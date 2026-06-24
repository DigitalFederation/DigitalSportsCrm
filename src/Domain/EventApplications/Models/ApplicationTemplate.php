<?php

namespace Domain\EventApplications\Models;

use App\Models\User;
use Domain\EvtEvents\Models\Sport;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string|null $state
 * @property bool|null $hasEntityApplied
 * @property EventApplication|null $existingApplication
 */
class ApplicationTemplate extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'application_templates';

    protected static function newFactory(): \Database\Factories\Domain\EventApplications\ApplicationTemplateFactory
    {
        return \Database\Factories\Domain\EventApplications\ApplicationTemplateFactory::new();
    }

    protected $fillable = [
        'name',
        'event_type',
        'sport_id',
        'event_category',
        'registration_type',
        'category',
        'age_group',
        'submission_start_date',
        'submission_end_date',
        'event_start_date',
        'event_end_date',
        'description',
        'target_audience',
        'is_active',
        'state',
        'max_applications',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'submission_start_date' => 'date',
            'submission_end_date' => 'date',
            'event_start_date' => 'date',
            'event_end_date' => 'date',
            'is_active' => 'boolean',
            'state' => 'string',
            'max_applications' => 'integer',
        ];
    }

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(EventApplication::class, 'template_id');
    }

    public function activeApplications(): HasMany
    {
        return $this->applications()->whereNull('deleted_at');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class, 'template_id');
    }

    public function getApplicationsCountAttribute(): int
    {
        return $this->activeApplications()->count();
    }

    public function hasApplied(int $entityId): bool
    {
        return $this->activeApplications()
            ->where('entity_id', $entityId)
            ->exists();
    }

    public function getEntityApplication(int $entityId): ?EventApplication
    {
        /** @var EventApplication|null $application */
        $application = $this->activeApplications()
            ->where('entity_id', $entityId)
            ->first();

        return $application;
    }

    public function isOpen(): bool
    {
        return $this->state === 'open';
    }

    public function isClosed(): bool
    {
        return $this->state === 'closed';
    }

    public function isDraft(): bool
    {
        return $this->state === 'draft';
    }

    public function isArchived(): bool
    {
        return $this->state === 'archived';
    }

    public function getStateColorAttribute(): string
    {
        return match ($this->state) {
            'open' => 'emerald',
            'closed' => 'slate',
            'draft' => 'amber',
            'archived' => 'gray',
            default => 'slate',
        };
    }
}
