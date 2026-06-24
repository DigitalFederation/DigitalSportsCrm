<?php

namespace Domain\Documents\States;

class DraftDocumentState extends DocumentState
{
    public function name(): string
    {
        return 'draft';
    }

    public function isPaid(): bool
    {
        return false;
    }

    public function isDraft(): bool
    {
        return true;
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
        return 'draft-state';
    }
}
