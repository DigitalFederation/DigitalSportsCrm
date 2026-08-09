<?php

declare(strict_types=1);

const STATES_URL = 'https://servicodados.ibge.gov.br/api/v1/localidades/estados?orderBy=nome';
const MUNICIPALITIES_URL = 'https://servicodados.ibge.gov.br/api/v1/localidades/municipios?orderBy=nome';
const EXPECTED_STATES = 27;
const MINIMUM_LOCALITIES = 5571;

$root = dirname(__DIR__);
$outputDirectory = $root.'/database/data/brazil';
$statesSource = $argv[1] ?? STATES_URL;
$municipalitiesSource = $argv[2] ?? MUNICIPALITIES_URL;

if (! is_dir($outputDirectory) && ! mkdir($outputDirectory, 0755, true) && ! is_dir($outputDirectory)) {
    throw new RuntimeException("Unable to create [{$outputDirectory}].");
}

$states = fetchJson($statesSource);
$municipalities = fetchJson($municipalitiesSource);

if (count($states) !== EXPECTED_STATES) {
    throw new RuntimeException('IBGE returned an unexpected number of federation units.');
}

if (count($municipalities) < MINIMUM_LOCALITIES) {
    throw new RuntimeException('IBGE returned fewer municipality-level localities than expected.');
}

$stateCodes = [];
$stateRows = [];

foreach ($states as $state) {
    $code = (string) ($state['id'] ?? '');
    $abbreviation = (string) ($state['sigla'] ?? '');
    $name = (string) ($state['nome'] ?? '');

    if (! preg_match('/^\d{2}$/', $code) || ! preg_match('/^[A-Z]{2}$/', $abbreviation) || $name === '') {
        throw new RuntimeException('IBGE returned a malformed federation unit.');
    }

    $stateCodes[$code] = true;
    $stateRows[] = [$code, $abbreviation, $name];
}

$municipalityRows = [];

foreach ($municipalities as $municipality) {
    $code = (string) ($municipality['id'] ?? '');
    $name = (string) ($municipality['nome'] ?? '');
    $stateCode = substr($code, 0, 2);

    if (! preg_match('/^\d{7}$/', $code) || $name === '' || ! isset($stateCodes[$stateCode])) {
        throw new RuntimeException("IBGE returned a malformed municipality-level locality [{$code}].");
    }

    $municipalityRows[] = [$code, $stateCode, $name];
}

writeCsv($outputDirectory.'/states.csv', ['ibge_code', 'abbreviation', 'name'], $stateRows);
writeCsv(
    $outputDirectory.'/municipalities.csv',
    ['ibge_code', 'state_ibge_code', 'name'],
    $municipalityRows
);

fwrite(STDOUT, sprintf(
    "Wrote %d federation units and %d municipality-level localities.\n",
    count($stateRows),
    count($municipalityRows)
));

/**
 * @return list<array<string, mixed>>
 */
function fetchJson(string $url): array
{
    $context = stream_context_create([
        'http' => [
            'timeout' => 60,
            'user_agent' => 'Digital Sports CRM geography dataset updater',
        ],
    ]);
    $contents = file_get_contents($url, false, $context);

    if ($contents === false) {
        throw new RuntimeException("Unable to download [{$url}].");
    }

    $data = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);

    if (! is_array($data) || ! array_is_list($data)) {
        throw new RuntimeException("IBGE returned an unexpected response for [{$url}].");
    }

    return $data;
}

/**
 * @param  list<string>  $headers
 * @param  list<list<string>>  $rows
 */
function writeCsv(string $path, array $headers, array $rows): void
{
    $temporaryPath = $path.'.tmp';
    $handle = fopen($temporaryPath, 'wb');

    if ($handle === false) {
        throw new RuntimeException("Unable to write [{$temporaryPath}].");
    }

    fputcsv($handle, $headers, ';', '"', '');

    foreach ($rows as $row) {
        fputcsv($handle, $row, ';', '"', '');
    }

    fclose($handle);

    if (! rename($temporaryPath, $path)) {
        throw new RuntimeException("Unable to replace [{$path}].");
    }
}
