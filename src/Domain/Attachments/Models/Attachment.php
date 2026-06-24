<?php

namespace Domain\Attachments\Models;

use App\Models\Committee;
use App\Models\Country;
use App\Models\Language;
use Database\Factories\AttachmentFactory;
use Domain\Certifications\Models\Certification;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\ProfessionalRole;
use Domain\Licenses\Models\License;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property int|string|null $owner_id
 * @property string|null $owner_type
 * @property int|string|null $recipient_id
 * @property string|null $recipient_name
 * @property string|null $recipient_type
 */
class Attachment extends Model implements hasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'language_id',
        'owner_type',
        'owner_id',
        'recipient_type',
        'recipient_id',
        'recipient_name',
        'category_id',
        'committee_id',
    ];

    protected static function newFactory(): AttachmentFactory
    {
        return AttachmentFactory::new();
    }

    protected static function boot()
    {
        parent::boot();
    }

    public function licenses(): BelongsToMany
    {
        return $this->belongsToMany(License::class, 'attachment_licenses');
    }

    public function certifications(): BelongsToMany
    {
        return $this->belongsToMany(Certification::class, 'attachment_certifications');
    }

    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'attachment_countries');
    }

    public function owner(): MorphTo
    {
        return $this->morphTo('owner');
    }

    public function recipient(): MorphTo
    {
        return $this->morphTo('recipient');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AttachmentCategory::class);
    }

    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }

    public function professionalRoles(): BelongsToMany
    {
        return $this->belongsToMany(ProfessionalRole::class, 'attachment_professional_roles');
    }

    public function filterFederation(): BelongsToMany
    {
        return $this->belongsToMany(Federation::class, 'attachment_filterfederations', 'attachment_id', 'federation_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * Query scope to filter by Name
     */
    public function scopeFilterName(Builder $query, string $name): Builder
    {
        return $query->where('name', 'like', '%' . $name . '%');
    }

    /**
     * Query scope to filter by Category
     */
    public function scopeFilterCategory(Builder $query, int $category_id): Builder
    {
        return $query->where(compact('category_id'));
    }

    /**
     * Query scope to filter by Language
     */
    public function scopeFilterLanguage($query, $languageId)
    {
        if ($languageId === 'all') {
            return $query;
        }

        return $query->where(function ($q) use ($languageId) {
            $q->where('language_id', $languageId)
                ->orWhereNull('language_id');
        });
    }

    /**
     * Query scope to filter by Date Start
     */
    public function scopeFilterDateStart(Builder $query, string $date): Builder
    {
        return $query->whereDate('created_at', '>=', Carbon::parse($date));
    }

    /**
     * Query scope to filter by Date End
     */
    public function scopeFilterDateEnd(Builder $query, string $date): Builder
    {
        return $query->whereDate('created_at', '<=', Carbon::parse($date));
    }

    /**
     * Scope a query to only include attachments of a given committee.
     *
     * @param  int  $committeeId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfCommittee(Builder $query, $committeeId)
    {
        return $query->where('committee_id', $committeeId);
    }

    /**
     * Get a descriptive string representing the recipient of the attachment.
     *
     * The description includes the recipient type (e.g., 'All Individuals', 'Filter Individuals')
     * and may also include additional filter criteria like Professional Roles, Licenses, and Certifications.
     *
     * @return string The recipient description
     */
    public function getRecipientDescriptionAttribute(): string
    {
        $translationKey = 'attachments.recipients.' . $this->recipient_name;
        $description = __($translationKey);

        if ($description === $translationKey) {
            $description = ucfirst(str_replace('_', ' ', $this->recipient_name));
        }

        if ($this->recipient_id) {
            $this->load('recipient');
            $description .= ': ' . ($this->recipient->name ?? '');
        }

        if ($this->recipient_type === 'individual') {

            $roles = $this->professionalRoles()->wherePivot('attachment_id', $this->id)->pluck('name')->implode(', ');
            $licenses = $this->licenses()->wherePivot('attachment_id', $this->id)->pluck('name')->implode(', ');
            $certifications = $this->certifications()->wherePivot('attachment_id', $this->id)->pluck('name')->implode(', ');
            $federations = $this->filterFederation()->wherePivot('attachment_id', $this->id)->pluck('name')->implode(', ');

            $filters = [];

            if (! empty($roles)) {
                $filters[] = __('attachments.roles') . ": $roles";
            }

            if (! empty($licenses)) {
                $filters[] = __('attachments.licenses') . ": $licenses";
            }

            if (! empty($certifications)) {
                $filters[] = __('attachments.certifications') . ": $certifications";
            }

            if (! empty($federations)) {
                $filters[] = __('attachments.federations') . ": $federations";
            }

            $description .= ' (' . implode(', ', $filters) . ')';
        }

        return $description;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('default')
            ->useDisk('secure-media');

        $this->addMediaCollection('attachments')
            ->useDisk('secure-media');
    }

    public function addMediaFromFile($filePath, $collectionName = 'default')
    {
        if (file_exists($filePath)) {
            return $this->addMedia($filePath)->toMediaCollection($collectionName);
        }

        throw new \Exception("File not found: {$filePath}");
    }
}
