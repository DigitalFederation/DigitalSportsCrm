<?php

namespace App\Imports;

use App\Models\Country;
use Domain\Geographic\Models\District;
use Domain\Geographic\Models\Zone;
use Domain\Individuals\Actions\CreateIndividualAction;
use Domain\Individuals\DataTransferObject\IndividualData;
use Domain\Individuals\Models\Individual;
use Domain\Users\Actions\CreateUserAction;
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
use Spatie\Permission\Models\Role;

class IndividualImport implements SkipsOnFailure, ToCollection, WithChunkReading, WithHeadingRow, WithValidation
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

                // Validate individual data
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

                // Check for duplicates
                $duplicate = $this->checkDuplicate($mappedData);
                if ($duplicate) {
                    $this->skipped[$rowNumber] = [
                        'reason' => 'Duplicate individual found',
                        'data' => $mappedData,
                        'existing_id' => $duplicate->id,
                    ];
                    DB::rollBack();

                    continue;
                }

                // Create user account
                $createUserAction = new CreateUserAction;
                $userResult = $createUserAction([
                    'email' => $mappedData['email'],
                    'name' => $mappedData['email'],
                    'role' => 'INDIVIDUAL',
                ]);

                if (! $userResult || ! isset($userResult['user'])) {
                    $this->errors[$rowNumber] = ['Failed to create user account'];
                    $this->errorCount++;
                    DB::rollBack();

                    continue;
                }

                // Assign the individual-approved role to newly created users
                $approvedRole = Role::firstOrCreate(
                    ['name' => 'individual-approved', 'guard_name' => 'web']
                );
                $userResult['user']->assignRole($approvedRole);

                // Create individual
                $createIndividualAction = new CreateIndividualAction;
                $individual = $createIndividualAction(
                    IndividualData::fromArray($mappedData, $userResult['user']->id)
                );

                if ($individual) {
                    $this->imported[$rowNumber] = [
                        'individual' => $individual,
                        'data' => $mappedData,
                    ];
                    $this->successCount++;
                    DB::commit();
                } else {
                    $this->errors[$rowNumber] = ['Failed to create individual'];
                    $this->errorCount++;
                    DB::rollBack();
                }

            } catch (Exception $e) {
                DB::rollBack();
                Log::error('Individual import error on row ' . $rowNumber . ': ' . $e->getMessage());
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
                    'zone_ids' => $this->resolveZones($value),
                    'birthdate', 'doc_ref_validation_date' => $this->parseDate($value),
                    'gender' => $this->normalizeGender($value),
                    'name', 'surname' => $this->normalizeName($value),
                    'email' => strtolower(trim($value)),
                    'phone' => $this->normalizePhone($value),
                    'vat_number' => trim($value),
                    default => $value
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
        $requiredFields = ['name', 'surname', 'email', 'birthdate'];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $errors[] = "Missing required field: {$field}";
            }
        }

        // Email validation
        if (! empty($data['email']) && ! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format: {$data['email']}";
        }

        // Email uniqueness check
        if (! empty($data['email'])) {
            $exists = DB::table('users')->where('email', $data['email'])->exists();
            if ($exists) {
                $errors[] = "Email already exists: {$data['email']}";
            }
        }

        // Birthdate validation
        if (! empty($data['birthdate'])) {
            if ($data['birthdate'] >= now()) {
                $errors[] = 'Birthdate must be in the past';
            }
        }

        // Country validation (optional - if provided, validate it)
        if (! empty($data['country_id']) && ! is_numeric($data['country_id'])) {
            $warnings[] = "Invalid country: {$data['country_id']}, will use Main Federation country";
        }

        // Gender validation
        if (! empty($data['gender']) && ! in_array($data['gender'], ['male', 'female'])) {
            $warnings[] = "Unknown gender value: {$data['gender']}, will be set to null";
            unset($data['gender']);
        }

        // URL validations
        $urlFields = ['facebook_url', 'x_url', 'instagram_url', 'linkedin_url'];
        foreach ($urlFields as $field) {
            if (! empty($data[$field]) && ! filter_var($data[$field], FILTER_VALIDATE_URL)) {
                $warnings[] = "Invalid URL format for {$field}: {$data[$field]}";
                unset($data[$field]);
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    protected function checkDuplicate(array $data): ?Individual
    {
        if (empty($data['name']) || empty($data['surname']) || empty($data['birthdate']) || empty($data['country_id'])) {
            return null;
        }

        return Individual::where('name', $data['name'])
            ->where('surname', $data['surname'])
            ->where('birthdate', $data['birthdate'])
            ->where('country_id', $data['country_id'])
            ->whereNull('deleted_at')
            ->first();
    }

    protected function resolveCountry($value): ?int
    {
        if (is_numeric($value)) {
            // Check if country ID exists
            $exists = Country::where('id', $value)->exists();

            return $exists ? (int) $value : null;
        }

        // Try to find by name (LIKE is case-insensitive in MySQL by default)
        $country = Country::where('name', 'LIKE', '%' . trim($value) . '%')->first();

        return $country ? $country->id : null;
    }

    protected function resolveDistrict($value): ?int
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            $exists = District::where('id', $value)->where('is_active', true)->exists();

            return $exists ? (int) $value : null;
        }

        // Try to find by name or code
        $district = District::where('is_active', true)
            ->where(function ($query) use ($value) {
                $query->where('name', 'LIKE', '%' . trim($value) . '%')
                    ->orWhere('code', 'LIKE', trim($value));
            })
            ->first();

        return $district?->id;
    }

    protected function resolveZones($value): ?array
    {
        if (empty($value)) {
            return null;
        }

        // Support comma-separated zone names/codes/ids
        $zoneValues = array_map('trim', explode(',', $value));
        $zoneIds = [];

        foreach ($zoneValues as $zoneValue) {
            if (is_numeric($zoneValue)) {
                $exists = Zone::where('id', $zoneValue)->where('is_active', true)->exists();
                if ($exists) {
                    $zoneIds[] = (int) $zoneValue;
                }
            } else {
                $zone = Zone::where('is_active', true)
                    ->where(function ($query) use ($zoneValue) {
                        $query->where('name', 'LIKE', '%' . $zoneValue . '%')
                            ->orWhere('code', 'LIKE', $zoneValue);
                    })
                    ->first();

                if ($zone) {
                    $zoneIds[] = $zone->id;
                }
            }
        }

        return ! empty($zoneIds) ? $zoneIds : null;
    }

    protected function normalizePhone($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        // Remove spaces and normalize the phone number
        return preg_replace('/\s+/', '', trim($value));
    }

    protected function parseDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            // Handle Excel date serial numbers
            if (is_numeric($value)) {
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);

                return $date->format('Y-m-d');
            }

            // Handle various date formats
            $date = \Carbon\Carbon::parse($value);

            return $date->format('Y-m-d');
        } catch (Exception $e) {
            return null;
        }
    }

    protected function normalizeGender($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $value = strtolower(trim($value));

        return match ($value) {
            'm', 'male', 'masculino', 'homme' => 'male',
            'f', 'female', 'feminino', 'femme' => 'female',
            default => null
        };
    }

    protected function normalizeName($value): string
    {
        return trim(ucwords(strtolower($value)));
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function rules(): array
    {
        return [
            '*.name' => 'required|string|max:45',
            '*.surname' => 'required|string|max:45',
            '*.email' => 'required|email',
            '*.birthdate' => 'required|date|before:today',
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
                'label' => 'First Name',
                'required' => true,
                'type' => 'string',
                'max_length' => 45,
                'suggestions' => ['First Name', 'Given Name', 'Nome', 'Prénom'],
            ],
            'surname' => [
                'label' => 'Last Name',
                'required' => true,
                'type' => 'string',
                'max_length' => 45,
                'suggestions' => ['Last Name', 'Family Name', 'Sobrenome', 'Apelido', 'Nom'],
            ],
            'email' => [
                'label' => 'Email Address',
                'required' => true,
                'type' => 'email',
                'suggestions' => ['Email', 'E-mail', 'Email Address', 'Courriel'],
            ],
            'birthdate' => [
                'label' => 'Birth Date',
                'required' => true,
                'type' => 'date',
                'suggestions' => ['Birth Date', 'DOB', 'Data Nascimento', 'Date de Naissance'],
            ],
            'country_id' => [
                'label' => 'Country',
                'required' => false,
                'type' => 'country',
                'suggestions' => ['Country', 'Nationality', 'País', 'Pays'],
            ],
            'gender' => [
                'label' => 'Gender',
                'required' => false,
                'type' => 'enum',
                'options' => ['male', 'female', 'other'],
                'suggestions' => ['Gender', 'Sex', 'Sexo', 'Sexe'],
            ],
            'vat_number' => [
                'label' => 'NIF/VAT Number',
                'required' => false,
                'type' => 'string',
                'max_length' => 50,
                'suggestions' => ['NIF', 'VAT', 'VAT Number', 'Tax ID', 'Contribuinte', 'Numero Fiscal'],
            ],
            'phone' => [
                'label' => 'Phone',
                'required' => false,
                'type' => 'string',
                'max_length' => 50,
                'suggestions' => ['Phone', 'Telephone', 'Telefone', 'Mobile', 'Telemovel', 'Contact Number'],
            ],
            'district_id' => [
                'label' => 'District',
                'required' => false,
                'type' => 'district',
                'suggestions' => ['District', 'Distrito', 'Region', 'Regiao'],
            ],
            'zone_ids' => [
                'label' => 'Zone',
                'required' => false,
                'type' => 'zone',
                'suggestions' => ['Zone', 'Zona', 'Area'],
            ],
            'native_name' => [
                'label' => 'Full Name',
                'required' => false,
                'type' => 'string',
                'max_length' => 45,
                'suggestions' => ['Full Name', 'Nome Completo', 'Nom Complet', 'Native Name', 'Nome Nativo'],
            ],
            'address' => [
                'label' => 'Address',
                'required' => false,
                'type' => 'text',
                'suggestions' => ['Address', 'Endereço', 'Adresse'],
            ],
            'location' => [
                'label' => 'City/Location',
                'required' => false,
                'type' => 'string',
                'suggestions' => ['City', 'Location', 'Cidade', 'Ville'],
            ],
            'postal_code' => [
                'label' => 'Postal Code',
                'required' => false,
                'type' => 'string',
                'suggestions' => ['Postal Code', 'ZIP', 'CEP', 'Code Postal'],
            ],
            'doc_ref_type' => [
                'label' => 'Document Type',
                'required' => false,
                'type' => 'string',
                'suggestions' => ['Document Type', 'ID Type', 'Tipo Documento'],
            ],
            'doc_ref' => [
                'label' => 'Document Number',
                'required' => false,
                'type' => 'string',
                'max_length' => 45,
                'suggestions' => ['Document Number', 'ID Number', 'Número Documento'],
            ],
            'doc_ref_validation_date' => [
                'label' => 'Document Expiry',
                'required' => false,
                'type' => 'date',
                'suggestions' => ['Doc Expiry', 'ID Expiry', 'Validade Documento'],
            ],
            'facebook_url' => [
                'label' => 'Facebook URL',
                'required' => false,
                'type' => 'url',
                'suggestions' => ['Facebook', 'FB URL', 'Facebook Profile'],
            ],
            'x_url' => [
                'label' => 'X (Twitter) URL',
                'required' => false,
                'type' => 'url',
                'suggestions' => ['Twitter', 'X URL', 'X Profile'],
            ],
            'instagram_url' => [
                'label' => 'Instagram URL',
                'required' => false,
                'type' => 'url',
                'suggestions' => ['Instagram', 'IG URL', 'Instagram Profile'],
            ],
            'linkedin_url' => [
                'label' => 'LinkedIn URL',
                'required' => false,
                'type' => 'url',
                'suggestions' => ['LinkedIn', 'LI URL', 'LinkedIn Profile'],
            ],
            'member_number' => [
                'label' => 'Member Number',
                'required' => false,
                'type' => 'integer',
                'suggestions' => ['Member Number', 'Numero Membro', 'Numero Socio', 'Numero Filiado', 'Member Number', 'Nº Id'],
            ],
            'entity_member_number' => [
                'label' => 'Entity Member Number',
                'required' => false,
                'type' => 'string',
                'suggestions' => ['Entity Member Number', 'Entity Number', 'Club Number', 'Numero Entidade', 'Numero do Clube'],
            ],
        ];
    }
}
