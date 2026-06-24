<?php

namespace Support\Traits;

use Domain\Documents\Models\Document;
use Domain\Documents\States\PaidDocumentState;
use Domain\Documents\States\PendingDocumentState;
use Illuminate\Database\Eloquent\Builder;

/**
 * Trait for models that track payment status via Document details.
 *
 * Provides methods to query documents associated with the model via document_detail table,
 * check payment status, and filter by payment status in queries.
 *
 * Use scopeWithPaymentStatus() on list queries to avoid N+1 problems.
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasDocumentPaymentStatus
{
    /**
     * Get documents associated with this model via document details.
     * Documents are owned by Individuals/Entities, but details reference this model.
     *
     * @return \Illuminate\Database\Eloquent\Builder<Document>
     */
    public function documentsViaDetails(): Builder
    {
        return Document::whereHas('details', function ($q) {
            $q->where('owner_type', static::class)
                ->where('owner_id', $this->id);
        });
    }

    /**
     * Check if this model has an unpaid (pending) document.
     */
    public function hasUnpaidDocument(): bool
    {
        return $this->documentsViaDetails()
            ->where('status_class', PendingDocumentState::class)
            ->exists();
    }

    /**
     * Check if this model has a paid document.
     */
    public function hasPaidDocument(): bool
    {
        return $this->documentsViaDetails()
            ->where('status_class', PaidDocumentState::class)
            ->exists();
    }

    /**
     * Eager-load payment status flags via subqueries to avoid N+1 on list pages.
     *
     * Usage: Model::query()->withPaymentStatus()->paginate()
     */
    public function scopeWithPaymentStatus(Builder $query): Builder
    {
        $documentTable = (new Document)->getTable();
        $detailTable = 'document_detail';
        $modelTable = $this->getTable();

        $buildSubquery = function (string $statusClass) use ($documentTable, $detailTable, $modelTable) {
            return Document::query()
                ->selectRaw('1')
                ->join($detailTable, "{$detailTable}.document_id", '=', "{$documentTable}.id")
                ->where("{$detailTable}.owner_type", static::class)
                ->whereColumn("{$detailTable}.owner_id", "{$modelTable}.id")
                ->where("{$documentTable}.status_class", $statusClass)
                ->whereNull("{$documentTable}.deleted_at")
                ->whereNull("{$detailTable}.deleted_at")
                ->limit(1);
        };

        return $query->addSelect([
            'has_paid_doc' => $buildSubquery(PaidDocumentState::class),
            'has_pending_doc' => $buildSubquery(PendingDocumentState::class),
        ]);
    }

    /**
     * Get the payment status attribute.
     *
     * Uses pre-loaded subquery flags when available (from scopeWithPaymentStatus),
     * otherwise falls back to individual queries.
     *
     * @return string One of: 'paid', 'pending_payment', 'no_document'
     */
    public function getPaymentStatusAttribute(): string
    {
        if (array_key_exists('has_paid_doc', $this->getAttributes())) {
            if ($this->has_paid_doc) {
                return 'paid';
            }

            if ($this->has_pending_doc) {
                return 'pending_payment';
            }

            return 'no_document';
        }

        if ($this->hasPaidDocument()) {
            return 'paid';
        }

        if ($this->hasUnpaidDocument()) {
            return 'pending_payment';
        }

        return 'no_document';
    }

    /**
     * Scope to filter by payment status.
     *
     * @param  string  $status  One of: 'paid', 'pending_payment', 'no_document'
     */
    public function scopeFilterPaymentStatus(Builder $query, string $status): Builder
    {
        $documentTable = (new Document)->getTable();
        $detailTable = 'document_detail';
        $modelTable = $this->getTable();

        $baseSubquery = function ($statusClass = null) use ($documentTable, $detailTable, $modelTable) {
            $subquery = Document::query()
                ->select('document.id')
                ->join($detailTable, "{$detailTable}.document_id", '=', "{$documentTable}.id")
                ->where("{$detailTable}.owner_type", static::class)
                ->whereColumn("{$detailTable}.owner_id", "{$modelTable}.id")
                ->whereNull("{$documentTable}.deleted_at")
                ->whereNull("{$detailTable}.deleted_at");

            if ($statusClass) {
                $subquery->where("{$documentTable}.status_class", $statusClass);
            }

            return $subquery;
        };

        return match ($status) {
            'paid' => $query->whereExists($baseSubquery(PaidDocumentState::class)),
            'pending_payment' => $query->whereExists($baseSubquery(PendingDocumentState::class)),
            'no_document' => $query->whereNotExists($baseSubquery()),
            default => $query,
        };
    }
}
