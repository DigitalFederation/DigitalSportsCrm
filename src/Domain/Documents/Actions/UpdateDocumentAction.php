<?php

namespace Domain\Documents\Actions;

use Domain\Documents\DataTransferObject\DocumentData;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentType;
use Domain\Documents\States\DraftDocumentState;
use Exception;

class UpdateDocumentAction
{
    public function __invoke(Document $document, DocumentData $data): Document
    {
        // Ensure the document is in a Draft state before updating
        if ($document->status_class !== DraftDocumentState::class) {
            throw new Exception('The document must be in a Draft state to be updated.');
        }

        // If there is a change in type, fetch new DocumentType and associate
        if ($data->type_id !== $document->type_id) {
            $documentType = DocumentType::findOrFail($data->type_id);
            $document->type()->associate($documentType);
        }

        // Update the document fields
        $document->fill($data->toArray());
        $document->save();

        activity('document')
            ->performedOn($document)
            ->event('updated')
            ->log("Document {$document->number_extended} updated");

        return $document;
    }
}
