# Brazil geography reference data

The two CSV files in this directory are a normalized snapshot of the official
[IBGE Localidades API](https://servicodados.ibge.gov.br/api/docs/localidades):

- `states.csv`: 27 federation units from `/api/v1/localidades/estados`;
- `municipalities.csv`: 5,571 municipality-level localities from
  `/api/v1/localidades/municipios`.

Snapshot retrieved on 2026-08-09. The locality list includes the 5,569 Brazilian municipalities
plus Brasília (Distrito Federal) and Fernando de Noronha (state district), matching the records
published by the IBGE API for statistical dissemination. The application exposes all of them in
the municipality selector so that residents of those territories can provide an address.

Snapshot checksums:

- `states.csv`: `e148ac7a4b03f10df0be5d11e1725774bbbbd8ffb4ed51fb7843ae970c8d11fe`
- `municipalities.csv`: `4632ddcbd91dae7651ce48950abcb2a85ea9c3c1ba615692a06b34517c2f4424`

IBGE municipality codes contain seven digits. Their first two digits identify the federation unit,
as documented in the official
[IBGE area-code tables](https://cnae.ibge.gov.br/classificacoes/por-tema/codigo-de-areas/codigo-de-areas.html).

To refresh the snapshot:

```bash
php scripts/update-brazil-geography.php
```

Review the upstream territorial changes and update the expected record count in
`BrazilGeographySeeder` before committing a new snapshot. Never make installation seeding depend on
the remote service; the checked-in CSV files are the installation source.
