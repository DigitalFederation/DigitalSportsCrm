<?php

namespace Domain\OfficialDocuments\States;

use Domain\OfficialDocuments\Models\OfficialDocument;

abstract class OfficialDocumentState
{
    protected OfficialDocument $document;

    public function __construct(OfficialDocument $document)
    {
        $this->document = $document;
    }

    abstract public function name(): string;

    abstract public function isPending(): bool;

    abstract public function isActive(): bool;

    abstract public function isExpired(): bool;

    abstract public function isRejected(): bool;

    abstract public function color(): string;
}
