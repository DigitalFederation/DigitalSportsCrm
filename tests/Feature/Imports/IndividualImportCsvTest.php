<?php

use App\Jobs\GenerateModelQrCode;
use App\Models\Country;
use Domain\Entities\Models\Entity;
use Domain\Entities\States\ActiveEntityFederationState;
use Domain\Federations\Models\Federation;
use Domain\Imports\Actions\ValidateImportFileAction;
use Domain\Individuals\Actions\IndividualImportAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    \App\Models\Group::firstOrCreate(['code' => 'INDIVIDUAL'], ['name' => 'Individual']);

    Queue::fake([GenerateModelQrCode::class]);

    $this->country = Country::factory()->create(['name' => 'Portugal']);

    $this->defaultFederation = Federation::factory()->create([
        'is_default_federation' => true,
        'name' => 'Primary Federation',
        'country_id' => $this->country->id,
    ]);

    $this->localFederation = Federation::factory()->create([
        'is_local' => true,
        'name' => 'Acores',
    ]);

    $this->entity = Entity::factory()->create([
        'member_number' => 71,
        'name' => 'Clube Naval Test',
    ]);

    $this->entity->entityFederations()->create([
        'federation_id' => $this->localFederation->id,
        'status_class' => ActiveEntityFederationState::class,
    ]);

    $this->importAction = app(IndividualImportAction::class);
    $this->validateAction = app(ValidateImportFileAction::class);
});

test('semicolon delimited csv is parsed correctly', function () {
    $csvContent = "Nome;Apelido;Nome Completo;filiado;data_nascimento;Country;cc;nif;genero;morada;codigo_postal;localidade;distrito;telefone;telemovel;email;Clube Filiado;Nome Clube;Zona\n";
    $csvContent .= "Carlos;Silva;Carlos Alberto Silva;12345;15/06/1985;Portugal;123456789;987654321;Masculino;Rua das Flores 123;1000-001;Lisboa;Lisboa;;912345678;carlos.silva@example.com;71;Clube Naval Test;Acores\n";
    $csvContent .= "Maria;Santos;Maria Joana Santos;12346;22/03/1990;Portugal;987654321;123456789;Feminino;Av. da Liberdade 456;2000-002;Porto;Porto;;923456789;maria.santos@example.com;71;Clube Naval Test;Acores\n";

    $file = UploadedFile::fake()->createWithContent('test_import.csv', $csvContent);

    $analysis = $this->importAction->analyzeFile($file);

    expect($analysis['success'])->toBeTrue()
        ->and($analysis['headers'])->toContain('Nome')
        ->and($analysis['headers'])->toContain('Apelido')
        ->and($analysis['headers'])->toContain('email')
        ->and($analysis['row_count'])->toBe(2);
});

test('field mapping suggestions work for portuguese headers', function () {
    $csvContent = "Nome;Apelido;data_nascimento;email\n";
    $csvContent .= "Test;User;01/01/1990;test@example.com\n";

    $file = UploadedFile::fake()->createWithContent('test_mapping.csv', $csvContent);

    $analysis = $this->importAction->analyzeFile($file);

    expect($analysis['success'])->toBeTrue()
        ->and($analysis['suggested_mappings']['Nome']['suggested_field'])->toBe('name')
        ->and($analysis['suggested_mappings']['Nome']['confidence'])->toBe(100)
        ->and($analysis['suggested_mappings']['Apelido']['suggested_field'])->toBe('surname')
        ->and($analysis['suggested_mappings']['email']['suggested_field'])->toBe('email');
});

test('csv with bom is handled correctly', function () {
    $bom = "\xEF\xBB\xBF";
    $csvContent = $bom . "Nome;Apelido;data_nascimento;email\n";
    $csvContent .= "Carlos;Silva;15/06/1985;carlos@example.com\n";

    $file = UploadedFile::fake()->createWithContent('test_bom.csv', $csvContent);

    $analysis = $this->importAction->analyzeFile($file);

    expect($analysis['success'])->toBeTrue()
        ->and($analysis['headers'][0])->toBe('Nome')
        ->and($analysis['suggested_mappings']['Nome']['suggested_field'])->toBe('name');
});

test('full import validation with semicolon csv', function () {
    $csvContent = "Nome;Apelido;data_nascimento;email;genero\n";
    $csvContent .= "Carlos;Silva;1985-06-15;carlos.test@example.com;male\n";
    $csvContent .= "Maria;Santos;1990-03-22;maria.test@example.com;female\n";
    $csvContent .= "Joao;Ferreira;1988-11-10;joao.test@example.com;male\n";

    $file = UploadedFile::fake()->createWithContent('test_validation.csv', $csvContent);

    $fieldMapping = [
        'Nome' => 'name',
        'Apelido' => 'surname',
        'data_nascimento' => 'birthdate',
        'email' => 'email',
        'genero' => 'gender',
    ];

    $result = $this->validateAction->execute($file, $fieldMapping, 'individual');

    expect($result->totalRows)->toBe(3)
        ->and($result->validRows)->toBe(3)
        ->and($result->errorRows)->toBe(0)
        ->and($result->hasErrors)->toBeFalse();
});

test('country is auto-set from main federation when not provided', function () {
    $csvContent = "Nome;Apelido;data_nascimento;email\n";
    $csvContent .= "Carlos;Silva;1985-06-15;carlos.country@example.com\n";

    $file = UploadedFile::fake()->createWithContent('test_country.csv', $csvContent);

    $fieldMapping = [
        'Nome' => 'name',
        'Apelido' => 'surname',
        'data_nascimento' => 'birthdate',
        'email' => 'email',
    ];

    $result = $this->validateAction->execute($file, $fieldMapping, 'individual');

    expect($result->validRows)->toBe(1)
        ->and($result->errorRows)->toBe(0);
});

test('entity member number resolves correctly in csv import', function () {
    $csvContent = "Nome;Apelido;data_nascimento;email;Clube Filiado\n";
    $csvContent .= "Carlos;Silva;1985-06-15;carlos.entity@example.com;71\n";

    $file = UploadedFile::fake()->createWithContent('test_entity.csv', $csvContent);

    $fieldMapping = [
        'Nome' => 'name',
        'Apelido' => 'surname',
        'data_nascimento' => 'birthdate',
        'email' => 'email',
        'Clube Filiado' => 'entity_member_number',
    ];

    $result = $this->validateAction->execute($file, $fieldMapping, 'individual');

    expect($result->validRows)->toBe(1)
        ->and($result->errorRows)->toBe(0)
        ->and($result->validRecords)->toHaveCount(1);

    $validRecord = array_values($result->validRecords)[0];
    expect($validRecord['entity_id'])->toBe($this->entity->id);
});

test('invalid entity member number produces warning or error', function () {
    $csvContent = "Nome;Apelido;data_nascimento;email;Clube Filiado\n";
    $csvContent .= "Carlos;Silva;1985-06-15;carlos.invalid@example.com;99999\n";

    $file = UploadedFile::fake()->createWithContent('test_invalid_entity.csv', $csvContent);

    $fieldMapping = [
        'Nome' => 'name',
        'Apelido' => 'surname',
        'data_nascimento' => 'birthdate',
        'email' => 'email',
        'Clube Filiado' => 'entity_member_number',
    ];

    $result = $this->validateAction->execute($file, $fieldMapping, 'individual');

    expect($result->errorRows)->toBe(1);
});

test('csv with extra whitespace in values is trimmed', function () {
    $csvContent = "Nome;Apelido;data_nascimento;email\n";
    $csvContent .= "  Carlos  ;  Silva  ;1985-06-15;  carlos.whitespace@example.com  \n";

    $file = UploadedFile::fake()->createWithContent('test_whitespace.csv', $csvContent);

    $fieldMapping = [
        'Nome' => 'name',
        'Apelido' => 'surname',
        'data_nascimento' => 'birthdate',
        'email' => 'email',
    ];

    $result = $this->validateAction->execute($file, $fieldMapping, 'individual');

    expect($result->validRows)->toBe(1)
        ->and($result->errorRows)->toBe(0);

    $validRecord = array_values($result->validRecords)[0];
    expect($validRecord['name'])->toBe('Carlos')
        ->and($validRecord['surname'])->toBe('Silva')
        ->and($validRecord['email'])->toBe('carlos.whitespace@example.com');
});

test('missing required fields produce errors', function () {
    $csvContent = "Nome;Apelido;data_nascimento;email\n";
    $csvContent .= ";Silva;1985-06-15;missing.name@example.com\n";
    $csvContent .= "Carlos;;1985-06-15;missing.surname@example.com\n";
    $csvContent .= "Maria;Santos;;missing.birthdate@example.com\n";
    $csvContent .= "Joao;Ferreira;1988-11-10;\n";

    $file = UploadedFile::fake()->createWithContent('test_missing.csv', $csvContent);

    $fieldMapping = [
        'Nome' => 'name',
        'Apelido' => 'surname',
        'data_nascimento' => 'birthdate',
        'email' => 'email',
    ];

    $result = $this->validateAction->execute($file, $fieldMapping, 'individual');

    expect($result->errorRows)->toBe(4)
        ->and($result->validRows)->toBe(0);
});

test('existing email in database produces error', function () {
    // Validation only rejects emails that have both a User and a linked Individual
    $user = \App\Models\User::factory()->create([
        'email' => 'existing.user@example.com',
    ]);
    \Domain\Individuals\Models\Individual::factory()->create([
        'user_id' => $user->id,
    ]);

    $csvContent = "Nome;Apelido;data_nascimento;email\n";
    $csvContent .= "Carlos;Silva;1985-06-15;existing.user@example.com\n";

    $file = UploadedFile::fake()->createWithContent('test_existing.csv', $csvContent);

    $fieldMapping = [
        'Nome' => 'name',
        'Apelido' => 'surname',
        'data_nascimento' => 'birthdate',
        'email' => 'email',
    ];

    $result = $this->validateAction->execute($file, $fieldMapping, 'individual');

    expect($result->errorRows)->toBe(1);
});

test('member number field is mapped correctly', function () {
    $csvContent = "Nome;Apelido;data_nascimento;email;Numero Membro\n";
    $csvContent .= "Carlos;Silva;1985-06-15;carlos.member@example.com;MEM001\n";

    $file = UploadedFile::fake()->createWithContent('test_member.csv', $csvContent);

    $fieldMapping = [
        'Nome' => 'name',
        'Apelido' => 'surname',
        'data_nascimento' => 'birthdate',
        'email' => 'email',
        'Numero Membro' => 'member_number',
    ];

    $result = $this->validateAction->execute($file, $fieldMapping, 'individual');

    expect($result->validRows)->toBe(1);

    $validRecord = array_values($result->validRecords)[0];
    expect($validRecord['member_number'])->toBe('MEM001');
});

test('various date formats are parsed correctly', function () {
    $csvContent = "Nome;Apelido;data_nascimento;email\n";
    $csvContent .= "Carlos;Silva;15/07/1957;carlos.date1@example.com\n";
    $csvContent .= "Maria;Santos;22-03-1990;maria.date2@example.com\n";
    $csvContent .= "Joao;Ferreira;1988-11-10;joao.date3@example.com\n";
    $csvContent .= "Ana;Costa;1995/06/25;ana.date4@example.com\n";

    $file = UploadedFile::fake()->createWithContent('test_dates.csv', $csvContent);

    $fieldMapping = [
        'Nome' => 'name',
        'Apelido' => 'surname',
        'data_nascimento' => 'birthdate',
        'email' => 'email',
    ];

    $result = $this->validateAction->execute($file, $fieldMapping, 'individual');

    expect($result->validRows)->toBe(4)
        ->and($result->errorRows)->toBe(0);

    $validRecords = array_values($result->validRecords);
    expect($validRecords[0]['birthdate'])->toBe('1957-07-15')
        ->and($validRecords[1]['birthdate'])->toBe('1990-03-22')
        ->and($validRecords[2]['birthdate'])->toBe('1988-11-10')
        ->and($validRecords[3]['birthdate'])->toBe('1995-06-25');
});

test('invalid date format produces error', function () {
    $csvContent = "Nome;Apelido;data_nascimento;email\n";
    $csvContent .= "Carlos;Silva;not-a-date;carlos.invalid@example.com\n";
    $csvContent .= "Maria;Santos;abc123;maria.baddate@example.com\n";

    $file = UploadedFile::fake()->createWithContent('test_invalid_dates.csv', $csvContent);

    $fieldMapping = [
        'Nome' => 'name',
        'Apelido' => 'surname',
        'data_nascimento' => 'birthdate',
        'email' => 'email',
    ];

    $result = $this->validateAction->execute($file, $fieldMapping, 'individual');

    expect($result->errorRows)->toBe(2)
        ->and($result->validRows)->toBe(0);
});
