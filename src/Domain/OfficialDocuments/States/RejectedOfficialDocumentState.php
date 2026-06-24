<?php

namespace Domain\OfficialDocuments\States;

class RejectedOfficialDocumentState extends OfficialDocumentState
{
    public function name(): string
    {
        return __('states.rejected');
    }

    public function isExpired(): bool
    {
        return false;
    }

    public function isActive(): bool
    {
        return false;
    }

    public function isPending(): bool
    {
        return false;
    }

    public function isRejected(): bool
    {
        return true;
    }

    public function color(): string
    {
        return 'canceled-state';
    }
}
