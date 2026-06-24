<?php

namespace Domain\OfficialDocuments\States;

class ExpiredOfficialDocumentState extends OfficialDocumentState
{
    public function name(): string
    {
        return __('states.expired');
    }

    public function transitionToExpired(): void
    {
        $this->document->status_class = static::class;
        $this->document->save();
    }

    public function isExpired(): bool
    {
        return true;
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
        return false;
    }
    public function color(): string
    {
        return 'canceled-state';
    }
}
