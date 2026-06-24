<?php

namespace Domain\Documents\States;

use Domain\Documents\Models\Document;

abstract class DocumentState
{
    protected Document $document;

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    abstract public function name(): string;

    abstract public function isDraft(): bool;

    abstract public function isPaid(): bool;

    abstract public function isPending(): bool;

    abstract public function isCanceled(): bool;

    abstract public function isPartial(): bool;

    abstract public function color(): string;

    public static function getAvailableStates(): array
    {
        $availableStates = [
            DraftDocumentState::class,
            // PaidDocumentState::class,
            PendingDocumentState::class,
            CanceledDocumentState::class,
        ];

        $stateClasses = [];
        foreach ($availableStates as $stateClass) {
            $stateClasses[$stateClass] = (new $stateClass(new Document))->name();
        }

        return $stateClasses;
    }
}
