<?php

namespace Domain\Documents\Actions;

use Domain\Documents\DataTransferObject\DocumentDetailData;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentDetail;

class CreateDocumentDetailAction
{
    public function __invoke(Document $document, DocumentDetailData $documentDetailData): DocumentDetail
    {
        $documentDetailData->document_id = $document->id;
        $documentDetail = DocumentDetailData::toModel($documentDetailData);
        $documentDetail->save();

        return $documentDetail;
    }
}
