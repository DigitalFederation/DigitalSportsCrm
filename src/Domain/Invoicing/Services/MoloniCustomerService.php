<?php

namespace Domain\Invoicing\Services;

use Domain\Entities\Models\Entity;
use Domain\Individuals\Models\Individual;
use Domain\Invoicing\Models\MoloniCustomer;
use Illuminate\Support\Facades\Log;

class MoloniCustomerService
{
    public function __construct(
        private MoloniClient $client,
        private MoloniSettingsService $settingsService
    ) {}

    public function findOrCreate(Individual|Entity $owner): int
    {
        $moloniCustomer = MoloniCustomer::findByOwner($owner);

        if ($moloniCustomer) {
            Log::debug('MoloniCustomerService: Found cached customer', [
                'owner_type' => get_class($owner),
                'owner_id' => $owner->id,
                'moloni_customer_id' => $moloniCustomer->moloni_customer_id,
            ]);

            return $moloniCustomer->moloni_customer_id;
        }

        $vatNumber = $this->getVatNumber($owner);

        if ($vatNumber && $vatNumber !== '999999990') {
            $existingCustomer = $this->findByVat($vatNumber);
            if ($existingCustomer) {
                Log::info('MoloniCustomerService: Found existing Moloni customer by VAT', [
                    'vat' => $vatNumber,
                    'moloni_customer_id' => $existingCustomer['customer_id'],
                ]);

                $this->cacheCustomerMapping($owner, $existingCustomer['customer_id'], $vatNumber, $existingCustomer['name'] ?? null);

                return $existingCustomer['customer_id'];
            }
        }

        return $this->createCustomer($owner);
    }

    private function findByVat(string $vatNumber): ?array
    {
        try {
            $response = $this->client->post('customers/getByVat/', [
                'vat' => $vatNumber,
            ]);

            // Moloni API returns an array of customers, check first result
            if (! empty($response) && isset($response[0]['customer_id'])) {
                return $response[0];
            }

            return null;
        } catch (\Exception $e) {
            Log::warning('MoloniCustomerService: Error searching customer by VAT', [
                'vat' => $vatNumber,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function createCustomer(Individual|Entity $owner): int
    {
        $data = $this->buildCustomerData($owner);

        Log::info('MoloniCustomerService: Creating new customer in Moloni', [
            'owner_type' => get_class($owner),
            'owner_id' => $owner->id,
            'customer_name' => $data['name'],
        ]);

        $response = $this->client->post('customers/insert/', $data);

        if (! isset($response['customer_id'])) {
            $errorDetails = $this->extractErrorDetails($response);

            Log::error('MoloniCustomerService: Failed to create customer', [
                'response' => $response,
                'error_details' => $errorDetails,
            ]);

            throw new \RuntimeException('Failed to create Moloni customer: ' . $errorDetails);
        }

        $this->cacheCustomerMapping(
            $owner,
            $response['customer_id'],
            $data['vat'] ?? null,
            $data['name']
        );

        Log::info('MoloniCustomerService: Customer created successfully', [
            'moloni_customer_id' => $response['customer_id'],
        ]);

        return $response['customer_id'];
    }

    private function buildCustomerData(Individual|Entity $owner): array
    {
        $isIndividual = $owner instanceof Individual;

        $vatNumber = $this->getVatNumber($owner);
        if (empty($vatNumber)) {
            $vatNumber = '999999990';
        }

        $name = $isIndividual
            ? $owner->full_name
            : ($owner->legal_name ?? $owner->name);

        $number = $isIndividual
            ? ($owner->member_number ?? $owner->member_code ?? (string) $owner->id)
            : ($owner->member_code ?? (string) $owner->id);

        return [
            'vat' => $vatNumber,
            'number' => substr($number, 0, 20),
            'name' => substr($name, 0, 100),
            'address' => substr($owner->address ?? 'N/A', 0, 100),
            'city' => substr($this->getCity($owner) ?: 'N/A', 0, 50),
            'zip_code' => $this->formatPostalCode($owner),
            'country_id' => $this->getCountryId($owner),
            'email' => $owner->email ?? '',
            'phone' => $this->getPhone($owner),
            'language_id' => 1,
            'maturity_date_id' => $this->settingsService->getDefaultMaturityDateId() ?? 0,
            'payment_method_id' => $this->settingsService->getPaymentMethodId() ?? 0,
            'copies' => 1,
            // Required fields with sensible defaults (Moloni API validation)
            'salesman_id' => 0,
            'payment_day' => 0,
            'discount' => 0.0,
            'credit_limit' => 0.0,
            'delivery_method_id' => 0,
        ];
    }

    private function getVatNumber(Individual|Entity $owner): ?string
    {
        $vat = $owner->vat_number ?? $owner->nif ?? null;

        // Validate Portuguese VAT format (9 digits)
        if ($vat && $this->isValidPortugueseVat($vat)) {
            return $vat;
        }

        return null;
    }

    private function isValidPortugueseVat(?string $vat): bool
    {
        if (empty($vat)) {
            return false;
        }

        // Remove any non-digit characters
        $digits = preg_replace('/[^0-9]/', '', $vat);

        // Portuguese VAT must be exactly 9 digits
        if (strlen($digits) !== 9) {
            return false;
        }

        // Check valid prefixes (1, 2, 3, 5, 6, 7, 8, 9)
        $firstDigit = $digits[0];
        if (! in_array($firstDigit, ['1', '2', '3', '5', '6', '7', '8', '9'])) {
            return false;
        }

        return true;
    }

    private function extractErrorDetails(array $response): string
    {
        if (empty($response)) {
            return 'no customer_id returned';
        }

        $errors = [];
        foreach ($response as $error) {
            if (isset($error['description'])) {
                $errors[] = $error['description'];
            }
        }

        return ! empty($errors) ? implode('; ', $errors) : 'no customer_id returned';
    }

    private function getCity(Individual|Entity $owner): string
    {
        if ($owner instanceof Individual) {
            return $owner->location ?? $owner->city ?? '';
        }

        return $owner->location ?? $owner->city ?? '';
    }

    private function getPostalCode(Individual|Entity $owner): string
    {
        return $owner->postal_code ?? $owner->zip_code ?? '';
    }

    private function formatPostalCode(Individual|Entity $owner): string
    {
        $postalCode = $this->getPostalCode($owner);

        if (empty($postalCode)) {
            return '';
        }

        $digits = preg_replace('/[^0-9]/', '', $postalCode);

        if (strlen($digits) === 7) {
            return substr($digits, 0, 4) . '-' . substr($digits, 4, 3);
        }

        if (strlen($digits) === 4) {
            return $digits . '-000';
        }

        if (preg_match('/^\d{4}-\d{3}$/', $postalCode)) {
            return $postalCode;
        }

        return '';
    }

    private function getPhone(Individual|Entity $owner): string
    {
        $phone = $owner->phone ?? $owner->mobile ?? '';

        if (empty($phone)) {
            return '';
        }

        $phone = preg_replace('/[^0-9+]/', '', $phone);

        if (strlen($phone) === 9 && ! str_starts_with($phone, '+')) {
            $phone = '+351' . $phone;
        }

        return $phone;
    }

    private function getCountryId(Individual|Entity $owner): int
    {
        return 1;
    }

    private function cacheCustomerMapping(Individual|Entity $owner, int $moloniCustomerId, ?string $vat, ?string $name): void
    {
        MoloniCustomer::updateOrCreate(
            [
                'customerable_type' => get_class($owner),
                'customerable_id' => $owner->id,
            ],
            [
                'moloni_customer_id' => $moloniCustomerId,
                'moloni_vat' => $vat,
                'moloni_name' => $name,
            ]
        );
    }
}
