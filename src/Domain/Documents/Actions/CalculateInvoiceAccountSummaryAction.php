<?php

namespace Domain\Documents\Actions;

use Domain\Documents\States\CanceledDocumentState;
use Domain\Documents\States\PaidDocumentState;
use Illuminate\Database\Eloquent\Builder;

class CalculateInvoiceAccountSummaryAction
{
    public function __construct() {}

    /**
     * Gets a collection of Documents of type Invoice
     * and returns an array with the calculation for
     *
     * total
     * total_paid
     * current_balance
     * invoices
     */
    /**
     * @param  Builder<\Domain\Documents\Models\Document>  $invoices
     */
    public static function execute(Builder $invoices): array
    {
        $allInvoices = $invoices->get();
        $filteredInvoicesForCalculation = $allInvoices->whereNotIn('status_class', [CanceledDocumentState::class]);

        $total = $filteredInvoicesForCalculation->sum('total_value');

        $totalPaid = $filteredInvoicesForCalculation
            ->filter(fn ($invoice) => $invoice->status_class === PaidDocumentState::class)
            ->sum('total_value');

        $currentBalance = $total - $totalPaid;

        return [
            'total' => (float) $total,
            'total_paid' => (float) $totalPaid,
            'current_balance' => (float) ($currentBalance * -1),
            'invoices' => $allInvoices, // Return the full collection for display
        ];
    }
}
