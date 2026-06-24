<?php

namespace App\Exports;

use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentDetail;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InvoicesExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * A collection of Document models (invoices)
     */
    protected Collection $documents;

    /**
     * Inject the invoices collection.
     */
    public function __construct(Collection $documents)
    {
        $this->documents = $documents;
    }

    /**
     * Return the collection of invoices.
     */
    public function collection(): Collection
    {
        return $this->documents->flatMap(function ($document) {
            return $document->details->map(function ($detail) use ($document) {
                $detail->document = $document;

                return $detail;
            });
        });
    }

    /**
     * Map each document detail to our desired columns.
     *
     * @param  \Domain\Documents\Models\DocumentDetail  $detail
     */
    public function map($detail): array
    {
        $document = $detail->document;

        // Get document owner information (for the header)
        [$ownerType, $ownerName, $ownerMemberCode, $federationName] = $this->getOwnerInformation($document);

        // Get detail owner information (for the line item)
        [$itemType, $itemName, $itemReference] = $this->getDetailOwnerInformation($detail);

        return [
            // Document Header Information
            $document->invoice_year,
            $document->invoice_extended,
            $document->created_at->format('Y-m-d'),
            $ownerType,
            $ownerName,
            $ownerMemberCode,
            $federationName,
            $document->getVatNumber(),
            $document->getAddress(),
            $document->getCity(),
            $document->getPostalCode(),
            $document->getCountry(),

            // Detail Line Information
            $itemType,
            $itemName,
            $itemReference,
            $detail->description,
            $detail->quantity,
            $detail->unit_value,
            $detail->quantity * $detail->unit_value,
            $detail->tax_percentage,
            $detail->tax_value,
            $document->total_value,

            // Payment Information
            $document->transactions->pluck('created_at')->filter()->map->format('Y-m-d')->join('; '),
            $document->stateName(),
        ];
    }

    protected function getOwnerInformation(Document $document): array
    {
        $owner = $document->owner;
        $type = $name = $memberCode = $federationName = '';

        if ($owner) {
            if ($owner instanceof Federation) {
                $type = 'Federation';
                $name = $owner->name;
                $memberCode = $owner->member_code ?? '';
                $federationName = $owner->name;
            } elseif ($owner instanceof Entity) {
                $type = 'Entity';
                $name = $owner->name;
                $memberCode = $owner->member_code ?? '';
                $federationName = $owner->federations
                    ->pluck('name')
                    ->filter()
                    ->join(', ');
            } elseif ($owner instanceof Individual) {
                $type = 'Individual';
                $name = $owner->getDisplayName();
                $memberCode = $owner->member_code ?? '';
                $federationName = $owner->federations->pluck('name')->join(', ');
            }
        } else {
            $type = 'Manual';
            $name = $document->customer_name ?? 'N/A';
        }

        return [$type, $name, $memberCode, $federationName];
    }

    protected function getDetailOwnerInformation(DocumentDetail $detail): array
    {
        // Default to manual if no owner exists
        $itemType = 'Manual Line';
        $itemName = '';
        $itemReference = '';

        if ($detail->owner) {
            // Use the readable owner type provided by the detail
            $itemType = $detail->readable_owner_type;

            switch (true) {
                case $detail->owner instanceof \Domain\Licenses\Models\LicenseAttributed:
                    // For licenses, fetch the related license's name or fallback to the license_name attribute.
                    $itemType = 'License';
                    if ($detail->owner->relationLoaded('license') && $detail->owner->license) {
                        $itemName = $detail->owner->license->name;
                    } else {
                        $itemName = $detail->owner->license_name;
                    }
                    $itemReference = $detail->owner->license_number ?? '';
                    break;

                case $detail->owner instanceof \Domain\Memberships\Models\Membership:
                    $itemType = 'Membership';
                    $itemName = $detail->owner->name;
                    if ($detail->owner->relationLoaded('membershipPlans') && $detail->owner->membershipPlans->isNotEmpty()) {
                        $itemReference = $detail->owner->membershipPlans->pluck('name')->join(', ');
                    }
                    break;

                case $detail->owner instanceof \Domain\Certifications\Models\CertificationSlot:
                    $itemType = 'Certification Slot';
                    if ($detail->owner->relationLoaded('certification') && $detail->owner->certification) {
                        $itemName = $detail->owner->certification->name;
                    }
                    if ($detail->owner->relationLoaded('slotType') && $detail->owner->slotType) {
                        $itemReference = $detail->owner->slotType->name;
                    }
                    break;

                case $detail->owner instanceof \Domain\EvtEvents\Models\Event:
                    $itemType = 'Event';
                    $itemName = $detail->owner->name;
                    $itemReference = $detail->owner->start_date->format('Y-m-d');
                    break;

                case $detail->owner instanceof \Domain\EvtEvents\Models\AthleteEnrollment:
                case $detail->owner instanceof \Domain\EvtEvents\Models\IndividualEnrollment:
                    $itemType = 'Event';
                    $detail->owner->load('event');
                    $itemName = $detail->owner->event->name ?? '';
                    break;
                case $detail->owner instanceof \Domain\EvtEvents\Models\Enrollment:
                    // For event-related enrollments, use the display name if available.
                    $itemType = 'Event';
                    $detail->owner->load('event');
                    $itemName = $detail->owner->event->name ?? '';
                    $itemReference = $detail->owner->id ?? '';
                    break;

                case $detail->owner instanceof Document:
                    $itemType = 'Related Document';
                    $itemName = $detail->owner->invoice_extended ?? $detail->owner->number_extended;
                    $itemReference = $detail->owner->invoice_extended ?? $detail->owner->number_extended;
                    break;

                default:
                    // Fallback: use the detail's description
                    $itemName = $detail->description || $detail->owner?->description;
                    break;
            }
        }

        return [$itemType, $itemName, $itemReference];
    }

    /**
     * Define the header row for the export.
     */
    public function headings(): array
    {
        return [
            // Document Header Information
            'Year',
            'Invoice Number',
            'Invoice Date',
            'Customer Type',
            'Customer Name',
            'International Code',
            'Federation',
            'VAT Number',
            'Address',
            'City',
            'Postal Code',
            'Country',

            // Detail Line Information
            'Item Type',
            'Item Name',
            'Item Reference',
            'Description',
            'Quantity',
            'Unit Price',
            'Line Item Total',
            'Tax %',
            'Tax Amount',
            'Document Total',

            // Payment Information
            'Payment Dates',
            'Status',
        ];
    }
}
