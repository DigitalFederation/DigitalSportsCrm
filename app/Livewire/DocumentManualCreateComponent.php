<?php

namespace App\Livewire;

use Domain\Documents\Actions\CalculateDocumentTotalAction;
use Domain\Documents\Actions\CreateDocumentAction;
use Domain\Documents\Actions\CreateDocumentDetailAction;
use Domain\Documents\Actions\GenerateDocumentNumberAction;
use Domain\Documents\Actions\UpdateDocumentAction;
use Domain\Documents\DataTransferObject\DocumentData;
use Domain\Documents\DataTransferObject\DocumentDetailData;
use Domain\Documents\Models\Document;
use Domain\Documents\Models\DocumentDetail;
use Domain\Documents\Models\DocumentType;
use Domain\Documents\States\DocumentState;
use Domain\Entities\Models\Entity;
use Domain\Federations\Actions\GetFederationsAndVirtualFederationEntities;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Actions\SearchIndividualsAction;
use Domain\Individuals\Models\Individual;
use Livewire\Component;

class DocumentManualCreateComponent extends Component
{
    public bool $isEditing = false;
    public Document $document;
    public array $documentStates;
    public $errorMessage = '';

    public int $selectedFederationId = 0;
    public int $selectedEntityId = 0;

    // Selected Individual (only via search)
    public string $selectedIndividualId = '';

    /**
     * Customer type determines which set of customer data is used.
     * Allowed values: 'federation', 'entity', 'individual', 'manual'
     */
    public string $customerType = 'federation';

    public $customerFederations;
    public $customerEntities;
    public array $documentDataArray = [];
    public array $documentDetailDataArray = [];
    public array $newDetail = [];
    public float $subtotal = 0.0;

    // Properties for Individual search
    public string $individualSearchTerm = '';
    public array $individualSearchResults = [];
    // Store the selected individual's data for preview
    public ?array $selectedIndividualData = null;

    public function mount(?Document $document = null)
    {
        $getCustomers = (new GetFederationsAndVirtualFederationEntities)->execute();
        $this->customerFederations = $getCustomers['federations'];
        $this->customerEntities = $getCustomers['entities'];

        if ($document && $document->exists) {
            $this->isEditing = true;
            $this->document = $document;

            // Set customerType based on the document owner.
            if (in_array($document->owner_type, [$this->morphAliasFor(Federation::class), Federation::class], true)) {
                $this->selectedFederationId = $document->owner_id;
                $this->customerType = 'federation';
            } elseif (in_array($document->owner_type, [$this->morphAliasFor(Entity::class), Entity::class], true)) {
                $this->selectedEntityId = $document->owner_id;
                $this->customerType = 'entity';
            } elseif (in_array($document->owner_type, [$this->morphAliasFor(Individual::class), Individual::class], true)) {
                $this->selectedIndividualId = $document->owner_id;
                $this->customerType = 'individual';
            } else {
                $this->customerType = 'manual';
            }

            $this->documentStates = DocumentState::getAvailableStates();
            $this->documentDataArray = DocumentData::fromModel($document)->toArray();
            $this->documentDataArray['status_class'] = get_class($document->state);
            $this->documentDetailDataArray = $this->document->details->map(function (DocumentDetail $detail) {
                return DocumentDetailData::fromModel($detail)->toArray();
            })->toArray();
        } else {

            $this->documentDataArray = (new DocumentData)->toArray();
            $this->customerType = 'federation'; // default
            // Generate document number
            $documentType = DocumentType::where('code', 'ORD')->first();
            if ($documentType) {
                $generatedNumber = (new GenerateDocumentNumberAction)($documentType);
                $this->documentDataArray = array_merge($this->documentDataArray, [
                    'number' => $generatedNumber['number'],
                    'number_pad' => $generatedNumber['number_pad'],
                    'number_year' => $generatedNumber['number_year'],
                    'number_extended' => $generatedNumber['number_extended'],
                ]);
            }
        }
    }

    /**
     * Update the individual search results when the search term changes.
     * A minimum of 3 characters is required to trigger the search.
     */
    public function updatedIndividualSearchTerm(string $value): void
    {
        if (strlen($value) >= 3) {
            try {
                $results = (new SearchIndividualsAction)($value)->toArray();
                $this->individualSearchResults = $results;
            } catch (\Exception $e) {
                logger()->error('Search Error:', [
                    'term' => $value,
                    'error' => $e->getMessage(),
                ]);
                $this->individualSearchResults = [];
            }
        } else {
            $this->individualSearchResults = [];
        }
    }

    /**
     * Sets the selected Individual.
     */
    public function selectIndividual(string $id, string $name): void
    {
        $this->selectedIndividualId = $id;
        $individual = Individual::find($id);
        if ($individual) {
            // Build an array of key fields to show in the preview card.
            $this->selectedIndividualData = [
                'member_code' => $individual->member_code,
                'name' => trim("{$individual->name} {$individual->surname}"),
                'native_name' => $individual->native_name,
                'email' => $individual->email,
                'birthdate' => $individual->birthdate,
                'gender' => $individual->gender,
                'address' => $individual->address ?? '',
                'city' => $individual->location ?? '',
                'postal_code' => $individual->postal_code ?? '',
                'country' => optional($individual->country)->name ?? '',
                'is_active' => true, // You might want to add logic for this
                'federation_name' => $individual->federations->first()?->name ?? '',
            ];

            // Pre-populate the invoice/customer fields with individual’s details.
            $this->documentDataArray['customer_name'] = $this->selectedIndividualData['name'];
            $this->documentDataArray['customer_address'] = $this->selectedIndividualData['address'];
            $this->documentDataArray['customer_postal_code'] = $this->selectedIndividualData['postal_code'];
            $this->documentDataArray['customer_city'] = $this->selectedIndividualData['city'];
            $this->documentDataArray['customer_country'] = $this->selectedIndividualData['country'];
        }
        $this->individualSearchTerm = $name;
        $this->individualSearchResults = [];
    }

    public function clearSelectedIndividual(): void
    {
        $this->selectedIndividualId = '';
        $this->selectedIndividualData = null;
        $this->individualSearchTerm = '';

        // Clear customer fields too
        $this->documentDataArray['customer_name'] = '';
        $this->documentDataArray['customer_address'] = '';
        $this->documentDataArray['customer_postal_code'] = '';
        $this->documentDataArray['customer_city'] = '';
        $this->documentDataArray['customer_country'] = '';
    }

    public function prepareNewDetail()
    {
        // Initialize the $newDetail array if needed
        $this->newDetail = (new DocumentDetailData(null, '', '', 1))->toArray(); // Reset newDetail

    }

    public function addNewDetail()
    {
        // A manually added detail must have a description.
        if (empty($this->newDetail['description'])) {
            $this->errorMessage = 'Please enter a description for the line item.';

            return;
        }

        // Calculate the total_value for the new detail
        $this->newDetail['total_value'] = $this->newDetail['quantity'] * $this->newDetail['unit_value'];
        $this->newDetail['tax_percentage'] = 0; // Set tax_percentage to 0 default
        $this->documentDetailDataArray[] = $this->newDetail;
        $this->recalculateSubtotal();
        $this->newDetail = (new DocumentDetailData(null, '', '', 1))->toArray(); // Reset newDetail

    }

    public function deleteDetail($index)
    {
        // Check if this is the last detail
        if (count($this->documentDetailDataArray) <= 1) {
            // Prevent deletion and notify the user
            $this->addError('errors', 'A document must have at least one detail.');

            return;
        }

        unset($this->documentDetailDataArray[$index]);
        $this->documentDetailDataArray = array_values($this->documentDetailDataArray); // Reindex array
        $this->recalculateSubtotal();
    }

    public function updated($propertyName)
    {
        if (preg_match('/documentDetailDataArray\.(\d+)\.(quantity|unit_value)/', $propertyName, $matches)) {
            $index = $matches[1];
            $this->documentDetailDataArray[$index]['total_value'] = $this->documentDetailDataArray[$index]['quantity'] * $this->documentDetailDataArray[$index]['unit_value'];

            // Recalculate the subtotal
            $this->recalculateSubtotal();
        } elseif (preg_match('/newDetail\.(quantity|unit_value)/', $propertyName)) {
            // Also update the newDetail's total_value if either quantity or unit_value changes
            $this->newDetail['total_value'] = $this->newDetail['quantity'] * $this->newDetail['unit_value'];
        }
    }
    public function recalculateSubtotal()
    {
        $this->subtotal = 0;
        $this->tax_value = 0;
        foreach ($this->documentDetailDataArray as $item) {
            $itemTotal = $item['quantity'] * $item['unit_value'];
            $this->subtotal += $itemTotal;
            $this->tax_value += ($itemTotal * $item['tax_percentage'] / 100);
        }
        $this->documentDataArray['net_value'] = $this->subtotal;
        $this->documentDataArray['tax_value'] = $this->tax_value;
        $this->documentDataArray['total_value'] = $this->subtotal + $this->tax_value;
    }

    public function saveDocument()
    {
        // Validate that exactly one customer type has been chosen.
        switch ($this->customerType) {
            case 'federation':
                if (! $this->selectedFederationId) {
                    $this->errorMessage = 'Please select a Federation.';

                    return;
                }
                $this->documentDataArray['owner_id'] = $this->selectedFederationId;
                $this->documentDataArray['owner_type'] = $this->morphAliasFor(Federation::class);
                break;
            case 'entity':
                if (! $this->selectedEntityId) {
                    $this->errorMessage = 'Please select an Entity.';

                    return;
                }
                $this->documentDataArray['owner_id'] = $this->selectedEntityId;
                $this->documentDataArray['owner_type'] = $this->morphAliasFor(Entity::class);
                break;
            case 'individual':
                if (! $this->selectedIndividualId) {
                    $this->errorMessage = 'Please select an Individual.';

                    return;
                }
                $this->documentDataArray['owner_id'] = $this->selectedIndividualId;
                $this->documentDataArray['owner_type'] = $this->morphAliasFor(Individual::class);
                break;
            case 'manual':
            default:
                if (empty($this->documentDataArray['customer_name'])) {
                    $this->errorMessage = 'Please enter the customer name.';

                    return;
                }
                break;
        }

        // Convert array data to DTOs
        $documentData = DocumentData::fromArray($this->documentDataArray);

        if ($this->isEditing) {
            $updateDocumentAction = new UpdateDocumentAction;
            $document = $updateDocumentAction($this->document, $documentData, true);
        } else {
            // Create Document
            $createDocumentAction = new CreateDocumentAction;
            $document = $createDocumentAction($documentData, 'ORD', true);
        }

        // Create Document Details
        $createDocumentDetailAction = new CreateDocumentDetailAction;
        $existingDetailIds = [];

        $detailsCollection = collect();  // Initialize an empty collection to store detail data

        foreach ($this->documentDetailDataArray as $detailDataArray) {
            // Populate fields from Document to DocumentDetail
            $detailDataArray['customer_name'] = $documentData->customer_name;
            // Convert array data to DTO
            $documentDetailData = DocumentDetailData::fromArray($detailDataArray);
            // Set the document_id on the detail data
            $documentDetailData->document_id = $document->id;
            // Create the document detail
            $detail = $createDocumentDetailAction($document, $documentDetailData);
            $existingDetailIds[] = $detail->id;
            $detailsCollection->push($detail);
        }

        // Calculate total_value, net_value, and tax_value
        $calculateDocumentTotalAction = new CalculateDocumentTotalAction;
        $totals = $calculateDocumentTotalAction($detailsCollection);

        // Update the document with the calculated totals
        $document->total_value = $totals['total_value'];
        $document->net_value = $totals['net_value'];
        $document->tax_value = $totals['tax_value'];
        $document->save();

        // Delete removed details
        if ($this->isEditing) {
            foreach ($this->document->details as $existingDetail) {
                if (! in_array($existingDetail->id, $existingDetailIds)) {
                    $existingDetail->delete();
                }
            }
        }

        return redirect()->route('admin.document.index');
    }

    public function render()
    {
        return view('livewire.document-manual-create-component');
    }

    private function morphAliasFor(string $modelClass): string
    {
        return (new $modelClass)->getMorphClass();
    }
}
