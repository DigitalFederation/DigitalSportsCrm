<?php

use App\Support\Committees;

/**
 * The license-purchase routing resolves entirely from config/committees.php,
 * so a deployment can define its own committees and get working purchase
 * routes/redirects without editing code.
 */
describe('diving default config', function () {
    it('resolves the generated route name for a configured entity page', function () {
        expect(Committees::purchaseRouteName('SPORT', 'entity'))
            ->toBe('entity.sport-license-purchase.index');
        expect(Committees::purchaseRouteName('DIVING', 'members'))
            ->toBe('entity.international-diving-member-license-purchase.index');
    });

    it('uses a custom entity_route when a committee has no generated entity page', function () {
        // DIVINGSERVICES has a members page and a wizard entity_route, no entity slug.
        expect(Committees::purchaseRouteName('DIVINGSERVICES', 'entity'))
            ->toBe('entity.diving_licenses.request');
        expect(Committees::purchaseRouteName('DIVINGSERVICES', 'members'))
            ->toBe('entity.national-diving-member-license-purchase.index');
    });

    it('redirects member purchases via the configured entity_license_via committee', function () {
        // DIVINGSERVICES members are gated by the DIVING entity license.
        expect(Committees::memberEntityRedirectRouteName('DIVINGSERVICES'))
            ->toBe('entity.international-diving-license-purchase.index');
        // Committees without an override fall back to their own entity page.
        expect(Committees::memberEntityRedirectRouteName('SPORT'))
            ->toBe('entity.sport-license-purchase.index');
    });

    it('exposes the first entity purchase page as the generic default', function () {
        expect(Committees::defaultEntityPurchaseRouteName())
            ->toBe('entity.sport-license-purchase.index');
    });

    it('resolves individual purchase pages under the individual route prefix', function () {
        expect(Committees::purchaseRouteName('SPORT', 'individual'))
            ->toBe('individual.sport-license-purchase.index');
        expect(Committees::purchaseRouteName('DIVINGSERVICES', 'individual'))
            ->toBe('individual.national-diving-license-purchase.index');
        expect(Committees::defaultPurchaseRouteName('individual'))
            ->toBe('individual.sport-license-purchase.index');
    });

    it('resolves licenses-attributed route names per portal', function () {
        expect(Committees::attributedRouteName('entity', 'SPORT', 'entity'))
            ->toBe('entity.sport-licenses-attributed.index');
        expect(Committees::attributedRouteName('entity', 'DIVINGSERVICES', 'members'))
            ->toBe('entity.national-diving-member-licenses-attributed.index');
        expect(Committees::attributedRouteName('federation', 'DIVING', 'individual'))
            ->toBe('federation.international-diving-individual-licenses-attributed.index');
        expect(Committees::attributedRouteName('admin', 'SCIENTIFIC', 'entity'))
            ->toBe('admin.scientific-entity-licenses-attributed.index');
        expect(Committees::attributedRouteName('individual', 'DIVINGSERVICES'))
            ->toBe('individual.national-diving-licenses-attributed.index');
    });

    it('falls back to a committee-label title when no convention key exists', function () {
        // A committee with no matching federation_* lang key resolves to a generic,
        // label-driven title rather than a raw translation key.
        app()->setLocale('en');
        config()->set('committees.list', [
            ['code' => 'YOUTH', 'name' => 'Youth Committee', 'is_international' => false, 'title_slug' => 'youth'],
        ]);

        expect(Committees::attributedTitleText('federation', 'YOUTH', 'entity'))
            ->toBe('Youth Committee Entity Licenses');
        expect(Committees::attributedTitleText('admin', 'YOUTH', 'individual'))
            ->toBe('Youth Committee Professional Licenses');
    });

    it('derives licenses-attributed titles by convention', function () {
        expect(Committees::attributedTitle('federation', 'DIVING', 'entity'))
            ->toBe('licenses.federation_cmas_diving_entity_licenses_title');
        expect(Committees::attributedTitle('admin', 'DIVINGSERVICES', 'individual'))
            ->toBe('licenses.admin_national_diving_individual_licenses_title');
        expect(Committees::attributedTitle('individual', 'SPORT'))
            ->toBe('licenses.individual_sport_licenses_title');
        // Entity portal titles are explicit in config.
        expect(Committees::attributedTitle('entity', 'SPORT', 'members'))
            ->toBe('licenses.Sport Licenses');
    });
});

describe('arbitrary non-diving config', function () {
    beforeEach(function () {
        config()->set('committees.list', [
            [
                'code' => 'SENIOR',
                'name' => 'Senior Committee',
                'is_international' => false,
                'purchase' => [
                    'entity' => ['slug' => 'senior-license-purchase'],
                    'members' => [
                        'slug' => 'senior-member-license-purchase',
                        'entity_license_via' => 'NATIONAL_TEAM',
                    ],
                ],
            ],
            [
                'code' => 'NATIONAL_TEAM',
                'name' => 'National Team Committee',
                'is_international' => true,
                'purchase' => [
                    'entity' => ['slug' => 'national-team-license-purchase'],
                ],
            ],
        ]);
    });

    it('generates purchase routes from a deployment-defined committee set', function () {
        expect(Committees::purchaseRouteName('SENIOR', 'entity'))
            ->toBe('entity.senior-license-purchase.index');
        expect(Committees::purchaseRouteName('SENIOR', 'members'))
            ->toBe('entity.senior-member-license-purchase.index');
        expect(Committees::purchaseRouteName('NATIONAL_TEAM', 'entity'))
            ->toBe('entity.national-team-license-purchase.index');
    });

    it('lists every registrable purchase page for the configured set', function () {
        expect(Committees::registrablePurchaseRoutes())->toEqual([
            ['code' => 'SENIOR', 'type' => 'entity', 'slug' => 'senior-license-purchase'],
            ['code' => 'SENIOR', 'type' => 'members', 'slug' => 'senior-member-license-purchase'],
            ['code' => 'NATIONAL_TEAM', 'type' => 'entity', 'slug' => 'national-team-license-purchase'],
        ]);
    });

    it('resolves member redirects against the deployment-defined committees', function () {
        expect(Committees::memberEntityRedirectRouteName('SENIOR'))
            ->toBe('entity.national-team-license-purchase.index');
        expect(Committees::defaultEntityPurchaseRouteName())
            ->toBe('entity.senior-license-purchase.index');
    });
});
