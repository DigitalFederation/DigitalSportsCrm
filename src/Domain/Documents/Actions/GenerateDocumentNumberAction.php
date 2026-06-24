<?php

namespace Domain\Documents\Actions;

use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentType;

/**
 * @mixin \Domain\Documents\Actions\GenerateDocumentNumberAction
 */
class GenerateDocumentNumberAction
{
    /**
     * Creates a document number taking into account the last
     * record inserted for that DocumentType
     */
    public function __invoke(DocumentType $type, ?int $number_year = null): array
    {
        // TODO - This should be configurable
        $number_pad = 6;
        if (empty($number_year)) {
            $number_year = today()->year;
        }

        $lastDocumentValue = Document::select('id', 'type_id', 'number')
            ->where('type_id', $type->id)
            ->where('number_year', $number_year)
            ->orderBy('number', 'desc')
            ->latest()
            ->value('number');

        $nextNumber = $lastDocumentValue ? $lastDocumentValue + 1 : 1;

        return [
            'number' => $nextNumber,
            'number_pad' => $number_pad,
            'number_year' => $number_year,
            'number_extended' => $type->prefix . '-' . str_pad(strval($nextNumber), $number_pad, '0', STR_PAD_LEFT) . '/' . $number_year,
        ];
    }
}
