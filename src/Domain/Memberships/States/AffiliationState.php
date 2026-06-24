<?php

namespace Domain\Memberships\States;

use Domain\Memberships\Models\Affiliation;

abstract class AffiliationState
{
    protected Affiliation $affiliation;

    public function __construct(Affiliation $affiliation)
    {
        $this->affiliation = $affiliation;
    }

    abstract public function name(): string;

    abstract public function color(): string;

    abstract public function isActive(): bool;

    public function canTransitionTo(string $stateClass): bool
    {
        // Define allowed transitions - can be customized per state
        return true;
    }
}
