<?php

namespace App\Imports;

use App\Models\Country;
use Domain\Entities\Actions\CreateEntityAction;
use Domain\Entities\DataTransferObject\EntityData;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Geographic\Models\District;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class EntityImport implements SkipsOnFailure, ToCollection, WithChunkReading, WithHeadingRow, WithValidation
{
    use SkipsFailures;

    protected array $fieldMapping;

    protected array $errors = [];

    protected array $warnings = [];

    protected array $imported = [];

    protected array $skipped = [];

    protected int $totalRows = 0;

    protected int $successCount = 0;

    protected int $errorCount = 0;

    protected int $warningCount = 0;

    public function __construct(array $fieldMapping = [])
    {
        $this->fieldMapping = $fieldMapping;
    }

    public function collection(Collection $rows)
    {
        $this->totalRows = $rows->count();

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because Excel starts at 1 and we have headers

            try {
                DB::beginTransaction();

                // Map external fields to internal fields
                $mappedData = $this->mapFields($row->toArray());

                // Validate entity data
                $validation = $this->validateRow($mappedData, $rowNumber);

                if (! $validation['valid']) {
                    $this->errors[$rowNumber] = $validation['errors'];
                    $this->errorCount++;
                    DB::rollBack();

                    continue;
                }

                if (! empty($validation['warnings'])) {
                    $this->warnings[$rowNumber] = $validation['warnings'];
                    $this->warningCount++;
                }

                // Check for duplicates by member_number or name+country
                $duplicate = $this->checkDuplicate($mappedData);
                if ($duplicate) {
                    $this->skipped[$rowNumber] = [
                        'reason' => 'Duplicate entity found',
                        'data' => $mappedData,
                        'existing_id' => $duplicate->id,
                    ];
                    DB::rollBack();

                    continue;
                }

                // Prepare data for entity creation
                $entityData = $this->prepareEntityData($mappedData);

                // Create entity
                $createEntityAction = new CreateEntityAction;
                $entity = $createEntityAction(EntityData::fromArray($entityData));

                if ($entity) {
                    $this->imported[$rowNumber] = [
                        'entity' => $entity,
                        'data' => $mappedData,
                    ];
                    $this->successCount++;
                    DB::commit();
                } else {
                    $this->errors[$rowNumber] = ['Failed to create entity'];
                    $this->errorCount++;
                    DB::rollBack();
                }

            } catch (Exception $e) {
                DB::rollBack();
                Log::error('Entity import error on row ' . $rowNumber . ': ' . $e->getMessage());
                $this->errors[$rowNumber] = [$e->getMessage()];
                $this->errorCount++;
            }
        }
    }

    protected function mapFields(array $row): array
    {
        $mapped = [];

        foreach ($this->fieldMapping as $externalField => $internalField) {
            if ($internalField && isset($row[$externalField])) {
                $value = $row[$externalField];

                // Apply transformations based on field type
                $mapped[$internalField] = match ($internalField) {
                    'country_id' => $this->resolveCountry($value),
                    'district_id' => $this->resolveDistrict($value),
                    'name', 'legal_name' => $this->normalizeName($value),
                    'email' => strtolower(trim($value)),
                    'member_number' => is_numeric($value) ? (int) $value : null,
                    default => trim($value)
                };
            }
        }

        return $mapped;
    }

    protected function validateRow(array $data, int $rowNumber): array
    {
        $errors = [];
        $warnings = [];

        // Required fields validation (country_id is auto-set from Main Federation)
        $requiredFields = ['name'];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $errors[] = "Missing required field: {$field}";
            }
        }

        // Email validation
        if (! empty($data['email']) && ! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format: {$data['email']}";
        }

        // VAT number validation (optional, but check format if provided)
        if (! empty($data['vat_number']) && strlen($data['vat_number']) < 5) {
            $warnings[] = "VAT number seems too short: {$data['vat_number']}";
        }

        // URL validations
        $urlFields = ['facebook_url', 'x_url', 'instagram_url', 'linkedin_url', 'website'];
        foreach ($urlFields as $field) {
            if (! empty($data[$field]) && ! filter_var($data[$field], FILTER_VALIDATE_URL)) {
                $warnings[] = "Invalid URL format for {$field}: {$data[$field]}";
                unset($data[$field]);
            }
        }

        // Member number validation
        if (! empty($data['member_number'])) {
            $exists = Entity::where('member_number', $data['member_number'])->exists();
            if ($exists) {
                $errors[] = "Member number already exists: {$data['member_number']}";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    protected function checkDuplicate(array $data): ?Entity
    {
        // First check by member_number if provided
        if (! empty($data['member_number'])) {
            $duplicate = Entity::where('member_number', $data['member_number'])
                ->whereNull('deleted_at')
                ->first();
            if ($duplicate) {
                return $duplicate;
            }
        }

        // Then check by name + country (use Main Federation's country)
        if (empty($data['name'])) {
            return null;
        }

        $countryId = $data['country_id'] ?? $this->getMainFederationCountryId();
        if (! $countryId) {
            return null;
        }

        return Entity::where('name', $data['name'])
            ->where('country_id', $countryId)
            ->whereNull('deleted_at')
            ->first();
    }

    protected function getMainFederationCountryId(): ?int
    {
        $mainFederation = Federation::where('is_default_federation', 1)->first();

        return $mainFederation?->country_id;
    }

    protected function resolveCountry($value): ?int
    {
        if (is_numeric($value)) {
            // Check if country ID exists
            $exists = Country::where('id', $value)->exists();

            return $exists ? (int) $value : null;
        }

        // Try to find by name (LIKE is case-insensitive in MySQL by default)
        // Escape LIKE wildcards to prevent SQL injection
        $escapedValue = str_replace(['%', '_'], ['\\%', '\\_'], trim($value));
        $country = Country::where('name', 'LIKE', '%' . $escapedValue . '%')->first();

        return $country ? $country->id : null;
    }

    protected function resolveDistrict($value): ?int
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            // Check if district ID exists
            $exists = District::where('id', $value)->exists();

            return $exists ? (int) $value : null;
        }

        // Try to find by name
        // Escape LIKE wildcards to prevent SQL injection
        $escapedValue = str_replace(['%', '_'], ['\\%', '\\_'], trim($value));
        $district = District::where('name', 'LIKE', '%' . $escapedValue . '%')->first();

        return $district ? $district->id : null;
    }

    protected function normalizeName($value): string
    {
        return trim(ucwords(strtolower($value)));
    }

    protected function prepareEntityData(array $mappedData): array
    {
        // Set legal_name to name if not provided
        if (empty($mappedData['legal_name'])) {
            $mappedData['legal_name'] = $mappedData['name'];
        }

        // Set default federation if not provided
        if (empty($mappedData['federation_id'])) {
            $defaultFederation = \Domain\Federations\Models\Federation::where('is_default_federation', 1)->first();
            $mappedData['federation_id'] = $defaultFederation ? $defaultFederation->id : null;
        }

        return $mappedData;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function rules(): array
    {
        return [
            '*.name' => 'required|string|max:255',
        ];
    }

    public function getResults(): array
    {
        return [
            'total_rows' => $this->totalRows,
            'success_count' => $this->successCount,
            'error_count' => $this->errorCount,
            'warning_count' => $this->warningCount,
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'imported' => $this->imported,
            'skipped' => $this->skipped,
        ];
    }

    public static function getSupportedFields(): array
    {
        return [
            'name' => [
                'label' => __('import.entity_field_name'),
                'required' => true,
                'type' => 'string',
                'max_length' => 255,
                'suggestions' => ['Entity Name', 'Name', 'Club Name', 'Organization', 'Nome', 'Nome da Entidade', 'Clube'],
            ],
            'legal_name' => [
                'label' => __('import.entity_field_legal_name'),
                'required' => false,
                'type' => 'string',
                'max_length' => 255,
                'suggestions' => ['Legal Name', 'Official Name', 'Nome Legal', 'Nome Oficial', 'Razao Social'],
            ],
            'member_number' => [
                'label' => __('import.entity_field_member_number'),
                'required' => false,
                'type' => 'integer',
                'suggestions' => ['Member Number', 'ID', 'Number', 'Numero', 'Numero de Membro', 'Codigo'],
            ],
            'vat_number' => [
                'label' => __('import.entity_field_vat_number'),
                'required' => false,
                'type' => 'string',
                'max_length' => 50,
                'suggestions' => ['VAT', 'VAT Number', 'Tax ID', 'NIF', 'NIPC', 'Contribuinte', 'Numero Contribuinte'],
            ],
            'email' => [
                'label' => __('import.entity_field_email'),
                'required' => false,
                'type' => 'email',
                'suggestions' => ['Email', 'E-mail', 'Email Address', 'Correio Eletronico'],
            ],
            'phone' => [
                'label' => __('import.entity_field_phone'),
                'required' => false,
                'type' => 'string',
                'suggestions' => ['Phone', 'Telephone', 'Tel', 'Telefone', 'Contacto'],
            ],
            'website' => [
                'label' => __('import.entity_field_website'),
                'required' => false,
                'type' => 'url',
                'suggestions' => ['Website', 'Web', 'URL', 'Site', 'Pagina Web'],
            ],
            'address' => [
                'label' => __('import.entity_field_address'),
                'required' => false,
                'type' => 'text',
                'suggestions' => ['Address', 'Street', 'Morada', 'Endereco', 'Rua'],
            ],
            'location' => [
                'label' => __('import.entity_field_location'),
                'required' => false,
                'type' => 'string',
                'suggestions' => ['City', 'Location', 'Town', 'Cidade', 'Localidade', 'Concelho'],
            ],
            'postal_code' => [
                'label' => __('import.entity_field_postal_code'),
                'required' => false,
                'type' => 'string',
                'suggestions' => ['Postal Code', 'ZIP', 'Postcode', 'Codigo Postal', 'CEP'],
            ],
            'district_id' => [
                'label' => __('import.entity_field_district'),
                'required' => false,
                'type' => 'district',
                'suggestions' => ['District', 'Region', 'Distrito', 'Regiao'],
            ],
            'federation_id' => [
                'label' => __('import.entity_field_federation'),
                'required' => false,
                'type' => 'federation',
                'suggestions' => ['Federation', 'Federation ID', 'Federacao', 'ID Federacao'],
            ],
            'legal_responsible_person' => [
                'label' => __('import.entity_field_legal_responsible'),
                'required' => false,
                'type' => 'string',
                'suggestions' => ['Legal Representative', 'Responsible', 'President', 'Director', 'Responsavel Legal', 'Presidente', 'Diretor'],
            ],
            'public_description' => [
                'label' => __('import.entity_field_public_description'),
                'required' => false,
                'type' => 'text',
                'suggestions' => ['Description', 'About', 'Bio', 'Descricao', 'Sobre'],
            ],
            'facebook_url' => [
                'label' => __('import.entity_field_facebook_url'),
                'required' => false,
                'type' => 'url',
                'suggestions' => ['Facebook', 'FB URL', 'Facebook Profile'],
            ],
            'x_url' => [
                'label' => __('import.entity_field_x_url'),
                'required' => false,
                'type' => 'url',
                'suggestions' => ['Twitter', 'X URL', 'X Profile'],
            ],
            'instagram_url' => [
                'label' => __('import.entity_field_instagram_url'),
                'required' => false,
                'type' => 'url',
                'suggestions' => ['Instagram', 'IG URL', 'Instagram Profile'],
            ],
            'linkedin_url' => [
                'label' => __('import.entity_field_linkedin_url'),
                'required' => false,
                'type' => 'url',
                'suggestions' => ['LinkedIn', 'LI URL', 'LinkedIn Profile'],
            ],
        ];
    }
}
