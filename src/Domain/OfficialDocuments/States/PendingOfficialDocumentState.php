<?php

namespace Domain\OfficialDocuments\States;

class PendingOfficialDocumentState extends OfficialDocumentState
{
    public function name(): string
    {
        return __('states.pending');
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
        return true;
    }

    public function isRejected(): bool
    {
        return false;
    }

    public function color(): string
    {
        return 'pending-state';
    }
}
