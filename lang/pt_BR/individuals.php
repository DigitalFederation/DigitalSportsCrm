<?php

$portalName = config('branding.primary.portal_name', 'Digital Sports CRM');
$primaryShortName = config('branding.primary.short_name', 'DF');
$internationalShortName = config('branding.international.short_name', 'IF');

return [
    'title' => 'Membros Individuais',
    'name' => 'Nome',
    'surname' => 'Apelido',
    'given_name' => 'Nome Próprio',
    'family_name' => 'Apelido',
    'nationality' => 'Nacionalidade',
    'member_number' => 'Nº Filiado',
    'id_number' => 'Nº ID',
    'affiliation_status' => 'Estado Filiação',
    'federation_portal' => $portalName,
    'cmas_portal' => "Portal {$internationalShortName}",
    'active' => 'Ativa',
    'inactive' => 'Inativa',
    'yes' => 'Sim',
    'no' => 'Não',
    'gender' => 'Sexo',
    'male' => 'Masculino',
    'female' => 'Feminino',
    'other' => 'Outro',
    'federation_portal_access' => "Acesso ao {$portalName}",
    'has_federation_portal_account' => "Tem Conta no {$portalName}",
    'federation_portal_description' => "Marque esta caixa se o individuo tem uma conta no {$portalName}",
    'cmas_portal_access' => "Acesso ao Portal {$internationalShortName}",
    'has_cmas_portal_account' => "Tem Conta no Portal {$internationalShortName}",
    'cmas_portal_description' => "Marque esta caixa se o individuo tem uma conta no Portal {$internationalShortName}",
    'national_fed_nr' => 'Nº Fed. Nacional',
    'birthdate' => 'Data de Nascimento',
    'instructors_leaders' => 'Instrutores e Dirigentes',
    'coachs' => 'Treinadores',
    'referees_judges' => 'Árbitros/Juízes',
    'create_individual' => 'Criar Indivíduo',
    'individuals_to_approve' => 'Indivíduos a Aprovar',
    'latest_entries' => 'Últimas entradas',

    // Federation membership
    'federation_and_organizations' => 'Federação e Associações',
    'federation_id' => 'ID',
    'federation_name' => 'Nome',
    'membership_status' => 'Estado de Membro',
    'confirm_disassociate_federation' => 'Tem a certeza que pretende desassociar-se desta federação?',
    'cannot_disassociate_main_federation' => 'Não e possivel desassociar-se da federação principal.',
    'member_number_already_taken' => 'Este Numero de Filiado ja esta atribuido a outro individuo.',
    'federation_edit_only' => '*Apenas a Federacao Nacional podera editar esta informacao',
];
