<?php

namespace Domain\Documents\States;

class PendingDocumentState extends DocumentState
{
    public function name(): string
    {
        return 'pending';
    }

    public function isPaid(): bool
    {
        return false;
    }

    public function isDraft(): bool
    {
        return false;
    }

    public function isCanceled(): bool
    {
        return false;
    }

    public function isPartial(): bool
    {
        return false;
    }

    public function isPending(): bool
    {
        return true;
    }

    public function color(): string
    {
        return 'pending-state';
    }
}
