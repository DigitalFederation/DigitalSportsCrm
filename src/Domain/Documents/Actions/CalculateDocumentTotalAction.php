<?php

namespace Domain\Documents\Actions;

use Illuminate\Support\Collection;

class CalculateDocumentTotalAction
{
    /**
     * Gets a collection of document details
     * and returns an array with the sum of:
     *
     * total_value
     * net_value
     * tax_value
     */
    public function __invoke(Collection $details): array
    {
        return [
            'total_value' => $details->sum('total_value'),
            'net_value' => $details->sum('net_value'),
            'tax_value' => $details->sum('tax_value'),
        ];
    }
}
