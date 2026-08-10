<?php

it('uses Brazilian identification and address terminology', function () {
    app()->setLocale('pt_PT');

    expect(__('individual.vat_number'))->toContain('NIF')
        ->and(__('individual.postal_code'))->toBe('Código postal');

    app()->setLocale('pt_BR');

    expect(__('individual.vat_number'))->toContain('CPF')
        ->and(__('individual.postal_code'))->toBe('CEP')
        ->and(__('individual.location'))->toBe('Bairro')
        ->and(__('individual.validation.vat_number_required'))->toContain('CPF');
});

it('uses Brazilian document types', function () {
    app()->setLocale('pt_BR');

    expect(__('individual.doc_types.identity_card'))->toContain('RG')
        ->and(__('individual.doc_types.citizen_card'))->toContain('CIN')
        ->and(__('individual.doc_types.foreign_identity_card'))->toContain('RNM');
});

it('uses Brazilian spelling for records and surnames', function () {
    app()->setLocale('pt_PT');

    expect(__('common.surname'))->toBe('Apelido');

    app()->setLocale('pt_BR');

    // "Apelido" means nickname in Brazil, and "registo" is the European spelling.
    expect(__('common.surname'))->toBe('Sobrenome')
        ->and(__('role_management.audit_logs'))->toBe('Registros de Auditoria')
        ->and(__('Register'))->toBe('Registrar');
});
