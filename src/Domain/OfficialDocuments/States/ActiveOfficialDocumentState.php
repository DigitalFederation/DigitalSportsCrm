<?php

namespace Domain\OfficialDocuments\States;

class ActiveOfficialDocumentState extends OfficialDocumentState
{
    public function name(): string
    {
        return __('states.active');
    }

    public function isExpired(): bool
    {
        return false;
    }

    public function isActive(): bool
    {
        return true;
    }

    public function isPending(): bool
    {
        return false;
    }

    public function isRejected(): bool
    {
        return false;
    }

    public function color(): string
    {
        return 'active-state';
    }
}
