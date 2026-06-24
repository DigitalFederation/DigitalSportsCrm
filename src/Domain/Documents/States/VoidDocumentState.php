<?php

namespace Domain\Documents\States;

class VoidDocumentState extends DocumentState
{
    public function name(): string
    {
        return 'void';
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

    public function isCanceled(): bool
    {
        return true;
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
