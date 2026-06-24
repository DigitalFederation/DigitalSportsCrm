<?php

namespace Domain\Documents\Models;

use App\Enums\CommitteeCodeEnum;
use App\Models\User;
use App\Traits\CreatedUpdatedBy;
use Database\Factories\DocumentFactory;
use Domain\Certifications\Models\CertificationAttributed;
use Domain\Documents\States\CanceledDocumentState;
use Domain\Documents\States\DocumentState;
use Domain\Documents\States\DraftDocumentState;
use Domain\Documents\States\PaidDocumentState;
use Domain\Documents\States\PendingDocumentState;
use Domain\Federations\Models\Federation;
use Domain\Invoicing\Models\MoloniInvoice;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Payments\Models\PaymentMethod;
use Domain\Payments\Models\PaymentTransaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Mpociot\Versionable\VersionableTrait;

/**
 * @property int|null $invoice_number
 * @property int|null $invoice_year
 * @property int|null $method_id
 * @property int|null $type_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property bool|null $is_view_only
 * @property string|float|int|null $amount_paid
 * @property string|null $customer_name
 * @property string|float|int|null $net_value
 * @property string|null $number_extended
 * @property string|null $number_pad
 * @property string|int|null $owner_id
 * @property string|null $owner_type
 * @property \Illuminate\Support\Collection<int, string> $owner_type_names
 * @property string|null $reference
 * @property string|float|int|null $tax_percentage
 * @property string|float|int|null $tax_value
 * @property string|float|int|null $total_value
 * @property mixed $status_class
 * @property mixed $state
 *
 * @method static paginate()
 * @method static belongsToFederation()
 * @method static whereNotNull(string $column)
 */
class Document extends Model
{
    use CreatedUpdatedBy;
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    // Verisonable Trait
    use VersionableTrait;

    /**
     * @var array
     */
    protected $dontVersionFields = ['status_class', 'created_by', 'updated_by'];

    protected static function newFactory(): DocumentFactory
    {
        return DocumentFactory::new();
    }

    protected $table = 'document';

    protected $appends = ['invoice_extended'];

    protected $fillable = [
        'type_id',
        'method_id',
        'status_class',
        'customer_name',
        'customer_city',
        'customer_address',
        'customer_country',
        'customer_postal_code',
        'tax_number',
        'net_value',
        'tax_value',
        'total_value',
        'due_date',
        'notes',
        'owner_type',
        'owner_id',
        'number',
        'number_pad',
        'number_year',
        'number_extended',
        'invoice_number',
        'invoice_year',
        'created_by',
        'updated_by',
        'amount_paid',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'method_id');
    }

    public function getStateAttribute(): DocumentState
    {
        return new $this->status_class($this);
    }

    public function owner(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'owner_type', 'owner_id');
    }

    /**
     * Returns related documents that have a detail with the same owner_id and owner_type as the original document
     */
    public function relatedDocuments()
    {
        return $this->whereHas('details', function ($query) {
            $query->where('owner_id', $this->id)
                ->where('owner_type', Document::class);
        });
    }

    public function getDisplayName(): string
    {
        return $this->invoice_extended ?? $this->number_extended;
    }

    public function isPaid(): bool
    {
        return $this->state->isPaid();
    }

    public function isDraft(): bool
    {
        return $this->state->isDraft();
    }

    public function isCanceled(): bool
    {
        return $this->state->isCanceled();
    }

    public function isPending(): bool
    {
        return $this->state->isPending();
    }

    public function stateName(): string
    {
        return $this->state->name();
    }

    public function stateColor(): string
    {
        return $this->state->color();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DocumentDetail::class);
    }

    public function documentDetails(): HasMany
    {
        return $this->details();
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function moloniInvoice(): HasOne
    {
        return $this->hasOne(MoloniInvoice::class);
    }

    public function scopeBelongsToFederation(Builder $query): Builder
    {
        return $query->whereHasMorph('owner', Federation::class, function (Builder $q) {
            return $q->where('id', auth()->user()->federations()->first()->id);
        });
    }

    /**
     * Scope a query to only include documents whose details reference a
     * CertificationAttributed or LicenseAttributed in the DIVING or SCIENTIFIC committees.
     */
    public function scopeHasDivingOrScientificCertOrLicense(Builder $query): Builder
    {
        return $this->applyCommitteeCertOrLicenseScope(
            $query,
            CommitteeCodeEnum::divingAndScientificValues()
        );
    }

    public function scopeHasDivingOrScientificCertOrLicenseForFederation(Builder $query, Federation $federation): Builder
    {
        if ($federation->isMainFederation()) {
            return $query->hasDivingOrScientificCertOrLicense();
        }

        $committeeCodes = $federation->committees()
            ->whereIn('committee.code', CommitteeCodeEnum::divingAndScientificValues())
            ->pluck('committee.code')
            ->map(fn (string $code) => strtoupper($code))
            ->values()
            ->all();

        if (empty($committeeCodes)) {
            return $query->whereRaw('1 = 0');
        }

        return $this->applyCommitteeCertOrLicenseScope(
            $query,
            $committeeCodes,
            self::accessibleFederationIds($federation)
        );
    }

    private function applyCommitteeCertOrLicenseScope(
        Builder $query,
        array $committeeCodes,
        ?array $federationIds = null
    ): Builder {
        $committeeCodes = array_values(array_unique(array_map('strtoupper', $committeeCodes)));

        if (empty($committeeCodes)) {
            return $query->whereRaw('1 = 0');
        }

        $certTypes = self::ownerTypeValuesFor(CertificationAttributed::class);
        $licenseTypes = self::ownerTypeValuesFor(LicenseAttributed::class);

        return $query->whereHas('details', function (Builder $detail) use ($certTypes, $licenseTypes, $committeeCodes, $federationIds) {
            $detail->where(function (Builder $d) use ($certTypes, $committeeCodes, $federationIds) {
                $d->whereIn('owner_type', $certTypes)
                    ->whereExists(function ($sub) use ($committeeCodes, $federationIds) {
                        $sub->select(DB::raw(1))
                            ->from('certification_attributed as ca')
                            ->join('certification as c', 'c.id', '=', 'ca.certification_id')
                            ->join('committee as com', 'com.id', '=', 'c.committee_id')
                            ->whereColumn('ca.id', 'document_detail.owner_id')
                            ->whereIn('com.code', $committeeCodes);

                        if ($federationIds !== null) {
                            $sub->whereIn('ca.federation_id', $federationIds);
                        }
                    });
            })->orWhere(function (Builder $d) use ($licenseTypes, $committeeCodes, $federationIds) {
                $d->whereIn('owner_type', $licenseTypes)
                    ->whereExists(function ($sub) use ($committeeCodes, $federationIds) {
                        $sub->select(DB::raw(1))
                            ->from('license_attributed as la')
                            ->join('license as l', 'l.id', '=', 'la.license_id')
                            ->join('committee as com', 'com.id', '=', 'l.committee_id')
                            ->whereColumn('la.id', 'document_detail.owner_id')
                            ->whereIn('com.code', $committeeCodes);

                        if ($federationIds !== null) {
                            $sub->whereIn('la.federation_id', $federationIds);
                        }
                    });
            });
        });
    }

    private static function accessibleFederationIds(Federation $federation): array
    {
        $ids = [$federation->id];

        $childIds = Federation::where('parent_id', $federation->id)
            ->pluck('id')
            ->all();

        $parentId = $federation->getAttribute('parent_id');
        if ($parentId) {
            $ids[] = $parentId;
        }

        return array_values(array_unique(array_merge($ids, $childIds)));
    }

    /**
     * Scope a query to only include results from status
     */
    public function scopeFilterStatus(Builder $query, string $status): Builder
    {
        switch ($status) {
            case 'paid':
                $status = PaidDocumentState::class;
                break;
            case 'draft':
                $status = DraftDocumentState::class;
                break;
            case 'pending':
                $status = PendingDocumentState::class;
                break;
            case 'canceled':
                $status = CanceledDocumentState::class;
                break;
        }

        return $query->where('status_class', $status);
    }

    /**
     * Scope a query to only include results from owner
     */
    public function scopeFilterFederations(Builder $query, int ...$federationId): Builder
    {
        return $query->whereHasMorph('owner', Federation::class, function (Builder $q) use ($federationId) {
            return $q->whereIn('id', $federationId);
        });
    }

    /**
     * Scope a query to find items with a specific value
     */
    public function scopeFilterTotal(Builder $query, string $total_value): Builder
    {
        return $query->where('total_value', $total_value);
    }

    /**
     * Scope a query to only include results from a given year
     */
    public function scopeFilterYears(Builder $query, int $year): Builder
    {
        return $query->where('number_year', $year);
    }

    public function scopeFilterDateStart(Builder $query, string $date): Builder
    {
        return $query->whereDate('created_at', '>=', Carbon::parse($date));
    }

    public function scopeFilterDateEnd(Builder $query, string $date): Builder
    {
        return $query->whereDate('created_at', '<=', Carbon::parse($date));
    }

    public function scopeFilterNumber($query, $number)
    {
        return $query->where('number_extended', 'LIKE', "%{$number}%");
    }

    public function scopeType(Builder $query, string $code): Builder
    {
        return $query->whereHas('type', function (Builder $q) use ($code) {
            return $q->where(compact('code'));
        });
    }

    public function getInvoiceExtendedAttribute(): ?string
    {
        if ($this->invoice_year && $this->invoice_number) {
            return $this->invoice_year . '/' . str_pad(strval($this->invoice_number), $this->number_pad, '0', STR_PAD_LEFT);
        }

        return null;
    }

    /**
     * Initiate payment for the document.
     *
     * This method checks if the document is payable according to the business logic,
     * then fetches the associated PaymentMethod model to initiate the payment.
     *
     * @return bool True if the payment is initiated successfully, false otherwise.
     */
    public function initiatePayment(): bool
    {
        // Check if the document is payable
        if (! $this->isPayable()) {
            return false;
        }

        $paymentMethod = $this->method;

        if (is_null($paymentMethod)) {

            return false;
        }

        $handler = $paymentMethod->getHandlerInstance($this);

        // Handle mixed return types from payment handlers
        $result = $handler->pay($this);

        // Convert result to boolean for backward compatibility
        return $result === true;
    }

    /**
     * Check if the document is payable.
     *
     * A document is considered payable if its current state is "Pending".
     *
     * @return bool True if the document is payable, false otherwise.
     */
    public function isPayable(): bool
    {
        return $this->isPending();
    }

    public function getOrganizationName(): string
    {
        if ($this->owner) {
            if ($this->owner instanceof \Domain\Individuals\Models\Individual) {
                return $this->owner->getFullNameAttribute();
            }

            return $this->owner->name ?? $this->owner->getDisplayName();
        }

        return $this->customer_name ?? 'N/A';
    }

    public function getVatNumber(): string
    {
        return $this->owner->vat_number ?? $this->tax_number ?? 'N/A';
    }

    public function getCity(): string
    {
        return $this->owner->location ?? $this->customer_city ?? 'N/A';
    }

    public function getAddress(): string
    {
        return $this->owner->address ?? $this->customer_address ?? 'N/A';
    }

    public function getPostalCode(): string
    {
        return $this->owner->zip_code ?? $this->customer_postal_code ?? 'N/A';
    }

    public function getCountry(): string
    {
        return $this->owner->country->name ?? $this->customer_country ?? 'N/A';
    }

    public function scopeFilterDocumentType(Builder $query, string $type): Builder
    {
        return $query->whereHas('type', function ($q) use ($type) {
            $q->where('id', $type);
        });
    }

    public function scopeFilterOwnerType(Builder $query, string $type): Builder
    {
        return $query->where(function ($q) use ($type) {
            switch ($type) {
                case 'federation':
                    $q->whereIn('owner_type', self::ownerTypeValuesFor(Federation::class));
                    break;
                case 'entity':
                    $q->whereIn('owner_type', self::ownerTypeValuesFor(\Domain\Entities\Models\Entity::class));
                    break;
                case 'individual':
                    $q->whereIn('owner_type', self::ownerTypeValuesFor(\Domain\Individuals\Models\Individual::class));
                    break;
                case 'manual':
                    $q->whereNull('owner_type');
                    break;
            }
        });
    }

    /**
     * Return both the configured morph alias and legacy full class owner type.
     */
    public static function ownerTypeValuesFor(string $ownerClass): array
    {
        $owner = new $ownerClass;
        $morphClass = method_exists($owner, 'getMorphClass') ? $owner->getMorphClass() : $ownerClass;

        return array_values(array_unique([$morphClass, $ownerClass]));
    }

    /**
     * Scope a query to only include documents whose owner (Federation, Entity, or Individual) has the given international code.
     */
    public function scopeFilterMemberCode(Builder $query, string $memberCode): Builder
    {
        return $query->where(function ($q) use ($memberCode) {
            $q->whereHasMorph(
                'owner',
                [
                    \Domain\Federations\Models\Federation::class,
                    \Domain\Entities\Models\Entity::class,
                    \Domain\Individuals\Models\Individual::class,
                ],
                function (Builder $ownerQuery) use ($memberCode) {
                    $ownerQuery->where('member_code', $memberCode);
                }
            );
        });
    }

    /**
     * Scope a query to filter documents by owner_id.
     * Supports both morph alias ('entity') and full class name for backwards compatibility.
     */
    public function scopeFilterOwnerId(Builder $query, string $ownerId): Builder
    {
        return $query->where('owner_id', $ownerId);
    }
}
