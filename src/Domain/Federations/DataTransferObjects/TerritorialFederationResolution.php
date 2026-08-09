<?php

namespace Domain\Federations\DataTransferObjects;

use Domain\Entities\Models\Entity;
use Domain\Federations\Enums\TerritorialAssignmentSource;
use Domain\Federations\Enums\TerritorialResolutionOutcome;
use Domain\Federations\Models\Federation;
use Domain\Geographic\Models\District;
use Domain\Geographic\Models\Zone;
use Illuminate\Support\Collection;

final readonly class TerritorialFederationResolution
{
    /**
     * @param  Collection<int, Federation>  $candidates
     */
    public function __construct(
        public TerritorialResolutionOutcome $outcome,
        public Collection $candidates,
        public ?TerritorialAssignmentSource $source = null,
        public ?Federation $federation = null,
        public ?Entity $entity = null,
        public ?Zone $zone = null,
        public ?District $district = null,
    ) {}

    public function isResolved(): bool
    {
        return $this->outcome === TerritorialResolutionOutcome::RESOLVED
            && $this->source !== null
            && $this->federation !== null;
    }
}
