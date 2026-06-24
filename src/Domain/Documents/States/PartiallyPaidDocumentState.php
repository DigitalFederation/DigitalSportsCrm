<?php

namespace Domain\Documents\States;

class PartiallyPaidDocumentState extends DocumentState
{
    public function name(): string
    {
        return 'partially_paid';
    }

    public function isDraft(): bool
    {
        return false;
    }

    public function isPaid(): bool
    {
        return false;
    }

    public function isPending(): bool
    {
        return false;
    }

    public function isPartial(): bool
    {
        return true;
    }

    public function isCanceled(): bool
    {
        return false;
    }

    public function color(): string
    {
        return 'pending';
    }
}
