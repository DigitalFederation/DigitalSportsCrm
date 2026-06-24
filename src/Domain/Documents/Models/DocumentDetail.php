<?php

namespace Domain\Documents\Models;

use Database\Factories\DocumentDetailFactory;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\EvtEvents\Models\AthleteEnrollment;
use Domain\EvtEvents\Models\Enrollment;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\IndividualEnrollment;
use Domain\Individuals\Models\Individual;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Memberships\Models\Membership;
use Domain\Memberships\Models\MemberSubscription;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int|null $owner_id
 * @property int|null $quantity
 * @property string|null $customer_name
 * @property string $description
 * @property string|null $owner_type
 * @property string|null $reference
 * @property string|float|int|null $net_value
 * @property string|float|int|null $tax_percentage
 * @property string|float|int|null $tax_value
 * @property string|float|int|null $total_value
 * @property string|float|int|null $unit_value
 */
class DocumentDetail extends Model
{
    use HasFactory;
    use SoftDeletes;

    // For activityLog
    protected static function booted()
    {
        static::deleting(function ($detail) {
            $detail->load('document');

            // Count how many details are currently associated with the document
            $count = $detail->document->details()->count();
            if ($count <= 1) {
                session()->flash('error', 'A document must have at least one detail.');
                // Throw an exception to halt the deletion
                throw new Exception('A document must have at least one detail.');
            }

            // Prepare a user-friendly message for the log entry.
            $description = "Detail with, description '{$detail->description}', and value {$detail->total_value} was deleted.";

            $activity = activity()
                ->performedOn($detail->document) // Associate the activity with the Document
                ->causedBy(auth()->user()) // or any other user you wish to record as the initiator
                ->withProperties([
                    'detail_id' => $detail->id,
                    'detail_description' => $detail->description,
                    'total_value' => $detail->total_value,
                    'deleted_at' => now()->toDateTimeString(), // Capture the deletion timestamp
                ])
                ->log($description); // Custom log message key
        });
    }
    protected static function newFactory(): DocumentDetailFactory
    {
        return DocumentDetailFactory::new();
    }

    protected $table = 'document_detail';

    protected $fillable = [
        'document_id',
        'description',
        'owner_id',
        'owner_type',
        'reference',
        'quantity',
        'unit_value',
        'net_value',
        'tax_value',
        'tax_percentage',
        'total_value',
        'is_debit',
        'customer_name',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    // In your DocumentDetail model

    public static function getOwnerTypeOptions(): array
    {
        return [
            LicenseAttributed::class => __('documents.categories.License'),
            Membership::class => __('documents.categories.Membership'),
            MemberSubscription::class => __('documents.categories.Membership'),
            Document::class => __('documents.categories.Document'),
            CertificationAttributed::class => __('documents.categories.Certification'),
            Event::class => __('documents.categories.Registration'),
            IndividualEnrollment::class => __('documents.categories.Registration'),
            AthleteEnrollment::class => __('documents.categories.Registration'),
            Enrollment::class => __('documents.categories.Registration'),
        ];
    }

    /**
     * Get unique owner type options for filter dropdowns
     * (deduplicated by translation value)
     */
    public static function getUniqueOwnerTypeOptions(): array
    {
        $options = self::getOwnerTypeOptions();
        $seen = [];
        $unique = [];

        foreach ($options as $class => $name) {
            if (! in_array($name, $seen)) {
                $seen[] = $name;
                $unique[$class] = $name;
            }
        }

        return $unique;
    }

    public function getReadableOwnerTypeAttribute(): string
    {
        // Check if owner_type is null and return "Manual Order"
        if (empty($this->owner_type)) {
            return __('documents.categories.Manual Order');
        }
        $readableNames = self::getOwnerTypeOptions();

        return $readableNames[$this->owner_type] ?? class_basename($this->owner_type);
    }

    /**
     * Get the Individual associated with this document detail (if any).
     * Checks the owner relationship for various types that may have an Individual.
     */
    public function getAssociatedIndividual(): ?Individual
    {
        if (! $this->relationLoaded('owner') || ! $this->owner) {
            return null;
        }

        $owner = $this->owner;

        // LicenseAttributed - check if owner is an Individual
        if ($owner instanceof LicenseAttributed) {
            if (! $owner->relationLoaded('owner')) {
                $owner->load('owner');
            }
            $licenseOwner = $owner->owner;
            if ($licenseOwner instanceof Individual) {
                return $licenseOwner;
            }
        }

        // MemberSubscription - check member relationship
        if ($owner instanceof MemberSubscription) {
            if (! $owner->relationLoaded('member')) {
                $owner->load('member');
            }
            $member = $owner->member;
            if ($member instanceof Individual) {
                return $member;
            }
        }

        // IndividualEnrollment - has individual relationship
        if ($owner instanceof IndividualEnrollment) {
            if (! $owner->relationLoaded('individual')) {
                $owner->load('individual');
            }

            return $owner->individual;
        }

        // AthleteEnrollment - has individual relationship
        if ($owner instanceof AthleteEnrollment) {
            if (! $owner->relationLoaded('individual')) {
                $owner->load('individual');
            }

            return $owner->individual;
        }

        return null;
    }

    /**
     * Get enhanced description with full name and affiliate number.
     * Replaces holder_name (first name only) with full name + affiliate number.
     */
    public function getEnhancedDescriptionAttribute(): string
    {
        $description = $this->description;
        $individual = $this->getAssociatedIndividual();

        if (! $individual) {
            return $description;
        }

        // Build the full identifier: "Hugo Rocha 12345"
        $fullName = trim($individual->name . ' ' . $individual->surname);
        if ($individual->affiliate_number) {
            $fullName .= ' ' . $individual->affiliate_number;
        }

        // Try to replace just the first name with full identifier
        // Description format is typically: "[License Name] - [First Name]"
        if ($individual->name && str_contains($description, ' - ' . $individual->name)) {
            return str_replace(
                ' - ' . $individual->name,
                ' - ' . $fullName,
                $description
            );
        }

        // If first name is at the end without separator
        if ($individual->name && str_ends_with($description, $individual->name)) {
            return substr($description, 0, -strlen($individual->name)) . $fullName;
        }

        // Fallback: append full identifier if name not found in description
        return $description . ' - ' . $fullName;
    }
}
