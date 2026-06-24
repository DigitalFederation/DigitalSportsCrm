<?php

namespace Domain\Documents\States;

class PaidDocumentState extends DocumentState
{
    public function name(): string
    {
        return 'paid';
    }

    public function isPaid(): bool
    {
        return true;
    }

    public function isDraft(): bool
    {
        return false;
    }

    public function isCanceled(): bool
    {
        return false;
    }

    public function isPending(): bool
    {
        return false;
    }

    public function isPartial(): bool
    {
        return false;
    }

    public function color(): string
    {
        return 'active-state';
    }
}
