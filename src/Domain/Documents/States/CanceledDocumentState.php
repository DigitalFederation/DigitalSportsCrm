<?php

namespace Domain\Documents\States;

class CanceledDocumentState extends DocumentState
{
    public function name(): string
    {
        return 'canceled';
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
        return true;
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
        return 'canceled-state';
    }
}
