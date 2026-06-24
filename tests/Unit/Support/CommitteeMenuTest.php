<?php

use App\Support\CommitteeMenu;

describe('diving default config', function () {
    it('generates one federation section per committee that declares a menu', function () {
        $sections = CommitteeMenu::sections('federation');

        expect(collect($sections)->pluck('committee')->all())
            ->toBe(['sport', 'diving', 'scientific']);
    });

    it('builds the sport section with its role breakdown and club label', function () {
        $sport = collect(CommitteeMenu::sections('federation'))->firstWhere('committee', 'sport');

        expect($sport['name'])->toBe('menu.federation.sport');
        expect($sport['icon'])->toBe('flag');
        expect($sport['can'])->toBe('access sport menu');

        $childNames = collect($sport['children'])->pluck('name')->all();
        expect($childNames)->toBe([
            'menu.federation.certifications',
            'menu.federation.clubs',
            'menu.federation.athletes',
            'menu.federation.coaches',
            'menu.federation.referees_judges',
            'menu.federation.official_documents',
        ]);

        // Child routes carry the committee + holder + professional filters.
        $athletes = collect($sport['children'])->firstWhere('name', 'menu.federation.athletes');
        expect($athletes['route'])->toBe([
            'federation.license-attributed.index',
            [
                'filter[committee]' => 'sport',
                'filter[filter_holder_type]' => 'individual',
                'filter[filter_professional]' => 'athlete',
            ],
        ]);
    });
});

describe('committee children (admin/individual lists)', function () {
    it('generates one filtered child per committee, labelled by committee', function () {
        $children = CommitteeMenu::committeeChildren(
            'admin.license-attributed.index',
            [],
            ['licenses-attributed']
        );

        expect(collect($children)->pluck('name')->all())->toBe([
            'menu.federation.sport',
            'menu.federation.diving',
            'menu.federation.scientific',
        ]);
        expect($children[0]['route'])->toBe([
            'admin.license-attributed.index',
            ['filter[committee]' => 'sport'],
        ]);
    });

    it('merges extra params into each child route', function () {
        $children = CommitteeMenu::committeeChildren(
            'federation.license-attributed.index',
            ['filter[filter_holder_type]' => 'entity'],
            []
        );

        expect($children[1]['route'][1])->toBe([
            'filter[committee]' => 'diving',
            'filter[filter_holder_type]' => 'entity',
        ]);
    });
});

describe('attachment children', function () {
    it('emits one attachment link per committee with the committee code', function () {
        $children = CommitteeMenu::attachmentChildren('federation');

        expect(collect($children)->pluck('name')->all())->toBe([
            'menu.federation.sport',
            'menu.federation.diving',
            'menu.federation.scientific',
        ]);
        // The committee code is emitted (resolved to an id by MenuSeeder at seed time).
        expect($children[0]['route'])->toBe([
            'federation.committee.attachments.index',
            ['committee' => 'SPORT'],
        ]);
    });
});

describe('arbitrary non-diving config', function () {
    it('adds a sidebar section for a committee defined only in config', function () {
        config()->set('committees.list', [
            [
                'code' => 'YOUTH',
                'name' => 'Youth Committee',
                'is_international' => false,
                'menu' => [
                    'label' => 'Youth',
                    'icon' => 'sparkles',
                    'order' => 1,
                    'professionals' => ['athlete' => 'Athletes'],
                ],
            ],
        ]);

        $sections = CommitteeMenu::sections('federation');

        expect($sections)->toHaveCount(1);
        expect($sections[0]['name'])->toBe('Youth');
        expect($sections[0]['committee'])->toBe('youth');
        expect($sections[0]['can'])->toBe('access youth menu');

        $childNames = collect($sections[0]['children'])->pluck('name')->all();
        expect($childNames)->toBe([
            'menu.federation.certifications', // generic child label
            'menu.federation.entities',       // default entities label (no clubs override)
            'Athletes',
            'menu.federation.official_documents',
        ]);
        // The new committee's filter is its lowercased code.
        expect($sections[0]['children'][0]['route'][1])->toBe(['filter[committee]' => 'youth']);
    });

    it('omits committees that declare no menu block', function () {
        config()->set('committees.list', [
            ['code' => 'NOMENU', 'name' => 'No Menu', 'is_international' => false],
        ]);

        expect(CommitteeMenu::sections('federation'))->toBe([]);
    });
});
