<?php

$portalName = config('branding.primary.portal_name', 'Digital Sports CRM');
$primaryShortName = config('branding.primary.short_name', 'DF');
$internationalShortName = config('branding.international.short_name', 'IF');

return [
    'title' => 'Einzelmitglieder',
    'name' => 'Name',
    'surname' => 'Nachname',
    'given_name' => 'Vorname',
    'family_name' => 'Familienname',
    'nationality' => 'Staatsangehörigkeit',
    'member_number' => 'Mitgliedsnummer',
    'id_number' => 'ID-Nummer',
    'affiliation_status' => 'Mitgliedschaftsstatus',
    'federation_portal' => $portalName,
    'cmas_portal' => "{$internationalShortName}-Portal",
    'active' => 'Aktiv',
    'inactive' => 'Inaktiv',
    'yes' => 'Ja',
    'no' => 'Nein',
    'gender' => 'Geschlecht',
    'male' => 'Männlich',
    'female' => 'Weiblich',
    'other' => 'Divers',
    'federation_portal_access' => "{$portalName}-Zugang",
    'has_federation_portal_account' => "Hat {$portalName}-Konto",
    'federation_portal_description' => "Aktivieren Sie dieses Kontrollkästchen, wenn die Einzelperson ein Konto bei {$portalName} hat",
    'cmas_portal_access' => "{$internationalShortName}-Portalzugang",
    'has_cmas_portal_account' => "Hat {$internationalShortName}-Portalkonto",
    'cmas_portal_description' => "Aktivieren Sie dieses Kontrollkästchen, wenn die Einzelperson ein Konto im {$internationalShortName}-Portal hat",
    'national_fed_nr' => 'Nationale Verbandsnr.',
    'birthdate' => 'Geburtsdatum',
    'instructors_leaders' => 'Ausbilder und Leiter',
    'coachs' => 'Trainer',
    'referees_judges' => 'Schiedsrichter/Kampfrichter',
    'create_individual' => 'Einzelperson erstellen',
    'individuals_to_approve' => 'Zu genehmigende Einzelpersonen',
    'latest_entries' => 'Neueste Einträge',

    // Federation membership
    'federation_and_organizations' => 'Verbände und Organisationen',
    'federation_id' => 'ID',
    'federation_name' => 'Name',
    'membership_status' => 'Mitgliedschaftsstatus',
    'confirm_disassociate_federation' => 'Sind Sie sicher, dass Sie die Zugehörigkeit zu diesem Verband aufheben möchten?',
    'cannot_disassociate_main_federation' => 'Sie können die Zugehörigkeit zum Hauptverband nicht aufheben.',
    'member_number_already_taken' => 'Diese Mitgliedsnummer ist bereits einer anderen Einzelperson zugewiesen.',
    'federation_edit_only' => '*Nur der nationale Verband kann diese Informationen bearbeiten',
];
