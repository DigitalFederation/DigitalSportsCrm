<?php

namespace App\Livewire\Public;

use App\Enums\CommitteeCodeEnum;
use Domain\Entities\Models\Entity;
use Domain\Federations\Models\Federation;
use Domain\Geographic\Models\District;
use Domain\Licenses\Models\License;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;

class LocationMap extends Component
{
    private const FILTER_PROPERTIES = [
        'selectedLicense',
        'selectedSportLicense',
        'selectedDivingLicense',
        'selectedDistrict',
        'searchTerm',
    ];

    public $selectedLicense = '';
    public $selectedSportLicense = '';
    public $selectedDivingLicense = '';
    public $selectedDistrict = '';
    public $selectedItem = null;
    public $searchTerm = '';
    public $isDrawerOpen = false;
    public $totalLocations = 0;
    public array $mapCenter = [
        'lat' => 0.0,
        'lng' => 0.0,
        'zoom' => 2,
    ];

    protected $queryString = [
        'selectedLicense' => ['except' => ''],
        'selectedSportLicense' => ['except' => ''],
        'selectedDivingLicense' => ['except' => ''],
        'selectedDistrict' => ['except' => ''],
        'searchTerm' => ['except' => ''],
    ];

    public function mount()
    {
        abort_unless((bool) config('public-map.enabled', true), 404);

        $this->normalizeFilters();
        $this->mapCenter = $this->configuredMapCenter();
        $this->dispatch('centerMap', $this->mapCenter);
        $this->dispatch('updateLocations', locations: $this->mapLocations);
    }

    public function updated($property)
    {
        if (in_array($property, self::FILTER_PROPERTIES, true)) {
            $this->normalizeFilters();
            $this->dispatch('updateLocations', locations: $this->mapLocations);
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['selectedLicense', 'selectedSportLicense', 'selectedDivingLicense', 'selectedDistrict', 'searchTerm']);
        $this->dispatch('updateLocations', locations: $this->mapLocations);
    }

    /**
     * Public method to initialize map data for JavaScript
     * Called by JS after map is ready
     */
    public function initializeMap(): void
    {
        $this->dispatch('updateLocations', locations: $this->mapLocations);
    }

    #[Computed]
    public function licenses()
    {
        return Cache::remember('active_entity_licenses', now()->addHour(), function () {
            return License::query()
                ->where('type_id', 1) // Filter for licenses of type "entity"
                ->whereHas('licensesAttributed', function ($query) {
                    $query->where('status_class', ActiveLicenseAttributedState::class);
                })
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
        });
    }

    #[Computed]
    public function sportLicenses(): Collection
    {
        $committeeCode = $this->committeeCode('sport', CommitteeCodeEnum::Sport->value);

        return Cache::remember("sport_entity_licenses_{$committeeCode}", now()->addHour(), function () use ($committeeCode) {
            return License::query()
                ->where('type_id', 1)
                ->whereHas('committee', fn ($q) => $q->where('code', $committeeCode))
                ->whereHas('licensesAttributed', function ($query) {
                    $query->where('status_class', ActiveLicenseAttributedState::class);
                })
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
        });
    }

    #[Computed]
    public function divingLicenses(): Collection
    {
        $committeeCode = $this->committeeCode('diving_services', CommitteeCodeEnum::DivingServices->value);

        return Cache::remember("diving_services_entity_licenses_{$committeeCode}", now()->addHour(), function () use ($committeeCode) {
            return License::query()
                ->where('type_id', 1)
                ->whereHas('committee', fn ($q) => $q->where('code', $committeeCode))
                ->whereHas('licensesAttributed', function ($query) {
                    $query->where('status_class', ActiveLicenseAttributedState::class);
                })
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
        });
    }

    #[Computed]
    public function districts(): Collection
    {
        $countryId = $this->countryId();

        if ($countryId === null) {
            return collect();
        }

        return Cache::remember("public_map_districts_{$countryId}", now()->addDay(), function () use ($countryId) {
            return District::query()
                ->where('country_id', $countryId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']);
        });
    }

    #[Computed]
    public function mapLocations(): array
    {
        if ($this->countryId() === null) {
            return [];
        }

        $cacheKey = 'public_map_locations_' . md5(implode('|', $this->normalizedFilterState())) . '_v4';

        return Cache::remember($cacheKey, now()->addMinutes(5), function () {
            $entities = $this->getFilteredEntities();
            $federations = $this->getFilteredFederations();

            return [...$entities, ...$federations];
        });
    }

    private function getFilteredEntities(): array
    {
        $countryId = $this->countryId();
        $selectedLicenseId = $this->integerFilterValue($this->selectedLicense);
        $selectedSportLicenseId = $this->integerFilterValue($this->selectedSportLicense);
        $selectedDivingLicenseId = $this->integerFilterValue($this->selectedDivingLicense);
        $selectedDistrictId = $this->integerFilterValue($this->selectedDistrict);
        $searchTerm = $this->normalizedSearchTerm($this->searchTerm);

        if ($countryId === null) {
            return [];
        }

        $entities = Entity::query()
            ->where('visible_in_map', true)
            ->where('country_id', $countryId)
            ->whereHas('licenses', function ($query) use ($selectedLicenseId) {
                $query->where('status_class', ActiveLicenseAttributedState::class)
                    ->when(
                        $selectedLicenseId,
                        fn ($q) => $q->where('license_id', $selectedLicenseId)
                    );
            })
            // Filter by Sport License (Clubes Desportivos)
            ->when($selectedSportLicenseId, function ($query) use ($selectedSportLicenseId) {
                $query->whereHas('licenses', function ($q) use ($selectedSportLicenseId) {
                    $q->where('status_class', ActiveLicenseAttributedState::class)
                        ->where('license_id', $selectedSportLicenseId);
                });
            })
            // Filter by Diving License (Entidades de Mergulho)
            ->when($selectedDivingLicenseId, function ($query) use ($selectedDivingLicenseId) {
                $query->whereHas('licenses', function ($q) use ($selectedDivingLicenseId) {
                    $q->where('status_class', ActiveLicenseAttributedState::class)
                        ->where('license_id', $selectedDivingLicenseId);
                });
            })
            // Filter by District
            ->when($selectedDistrictId, fn ($query) => $query->where('district_id', $selectedDistrictId))
            ->when(
                $searchTerm,
                fn ($query) => $query->where(function ($q) use ($searchTerm) {
                    $likeSearch = "%{$searchTerm}%";

                    $q->where('name', 'like', $likeSearch)
                        ->orWhere('location', 'like', $likeSearch)
                        ->orWhereHas(
                            'country',
                            fn ($q) => $q->where('name', 'like', $likeSearch)
                        );
                })
            )
            ->whereNotNull(['lat', 'lng'])
            ->with([
                'country',
                'district',
                'licenses' => fn ($q) => $q->where('status_class', ActiveLicenseAttributedState::class),
                'licenses.license',
                'federations',
            ])
            ->limit($this->maxResults())
            ->get()
            ->filter(function ($entity) {
                // Additional check to ensure we only keep entities with active licenses
                return $entity->licenses->isNotEmpty();
            })
            ->map(fn ($entity) => [
                'id' => $entity->id,
                'type' => 'entity',
                'name' => $entity->name,
                'lat' => (float) $entity->lat,
                'lng' => (float) $entity->lng,
                'licenses' => $entity->licenses->map(fn ($license) => $license->license?->name)->filter()->values(),
                'address' => $entity->address,
                'location' => $entity->location,
                'district' => $entity->district?->name,
                'country' => $entity->country?->name,
            ])
            ->toArray();

        return $entities;
    }

    private function getFilteredFederations(): array
    {
        $countryId = $this->countryId();
        $searchTerm = $this->normalizedSearchTerm($this->searchTerm);

        if ($countryId === null || ! (bool) config('public-map.include_federations', false)) {
            return [];
        }

        $federations = Federation::query()
            ->where('country_id', $countryId)
            ->when(
                $searchTerm,
                fn ($query) => $query->where(function ($q) use ($searchTerm) {
                    $likeSearch = "%{$searchTerm}%";

                    $q->where('name', 'like', $likeSearch)
                        ->orWhere('location', 'like', $likeSearch)
                        ->orWhereHas(
                            'country',
                            fn ($q) => $q->where('name', 'like', $likeSearch)
                        );
                })
            )
            ->whereNotNull(['lat', 'lng'])
            ->with([
                'country',
                'entities',
                'memberships.plans.committee',
            ])
            ->limit($this->maxResults())
            ->get()
            ->map(fn ($federation) => [
                'id' => $federation->id,
                'type' => 'federation',
                'name' => $federation->name,
                'lat' => (float) $federation->lat,
                'lng' => (float) $federation->lng,
                'address' => $federation->address,
                'location' => $federation->location,
                'country' => $federation->country?->name,
            ])
            ->toArray();

        return $federations;
    }

    #[Computed]
    public function federationsCount(): int
    {
        $countryId = $this->countryId();
        $searchTerm = $this->normalizedSearchTerm($this->searchTerm);

        if ($countryId === null || ! (bool) config('public-map.include_federations', false)) {
            return 0;
        }

        $cacheKey = sprintf('public_map_federations_count_%s_%s_v2', $countryId, md5($searchTerm));

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($countryId, $searchTerm) {
            return Federation::query()
                ->where('country_id', $countryId)
                ->when(
                    $searchTerm,
                    fn ($query) => $query->where(function ($q) use ($searchTerm) {
                        $likeSearch = "%{$searchTerm}%";

                        $q->where('name', 'like', $likeSearch)
                            ->orWhere('location', 'like', $likeSearch);
                    })
                )
                ->whereNotNull(['lat', 'lng'])
                ->count();
        });
    }

    #[Computed]
    public function entitiesCount(): int
    {
        $countryId = $this->countryId();
        $selectedLicenseId = $this->integerFilterValue($this->selectedLicense);
        $selectedSportLicenseId = $this->integerFilterValue($this->selectedSportLicense);
        $selectedDivingLicenseId = $this->integerFilterValue($this->selectedDivingLicense);
        $selectedDistrictId = $this->integerFilterValue($this->selectedDistrict);
        $searchTerm = $this->normalizedSearchTerm($this->searchTerm);

        if ($countryId === null) {
            return 0;
        }

        $cacheKey = 'public_map_entities_count_' . md5(implode('|', $this->normalizedFilterState())) . '_v3';

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($countryId, $selectedLicenseId, $selectedSportLicenseId, $selectedDivingLicenseId, $selectedDistrictId, $searchTerm) {
            return Entity::query()
                ->where('visible_in_map', true)
                ->where('country_id', $countryId)
                ->when($selectedLicenseId, fn ($query) => $query->whereHas('licenses', function ($query) use ($selectedLicenseId) {
                    $query->where('status_class', ActiveLicenseAttributedState::class)
                        ->where('license_id', $selectedLicenseId);
                }))
                ->when($selectedSportLicenseId, fn ($query) => $query->whereHas('licenses', function ($query) use ($selectedSportLicenseId) {
                    $query->where('status_class', ActiveLicenseAttributedState::class)
                        ->where('license_id', $selectedSportLicenseId);
                }))
                ->when($selectedDivingLicenseId, fn ($query) => $query->whereHas('licenses', function ($query) use ($selectedDivingLicenseId) {
                    $query->where('status_class', ActiveLicenseAttributedState::class)
                        ->where('license_id', $selectedDivingLicenseId);
                }))
                ->when($selectedDistrictId, fn ($query) => $query->where('district_id', $selectedDistrictId))
                ->when(
                    $searchTerm,
                    fn ($query) => $query->where(function ($q) use ($searchTerm) {
                        $likeSearch = "%{$searchTerm}%";

                        $q->where('name', 'like', $likeSearch)
                            ->orWhere('location', 'like', $likeSearch);
                    })
                )
                ->whereNotNull(['lat', 'lng'])
                ->count();
        });
    }

    public function showDetails($id, $type)
    {
        $id = is_numeric($id) ? (int) $id : null;
        $type = is_string($type) ? $type : '';

        if ($id === null || ! in_array($type, ['entity', 'federation'], true)) {
            $this->selectedItem = null;

            return;
        }

        $location = collect($this->mapLocations)->first(
            fn ($loc) => $loc['id'] === $id && $loc['type'] === $type
        );

        if (! $location) {
            $this->selectedItem = null;

            return;
        }

        $this->selectedItem = [
            'id' => $id,
            'type' => $type,
        ];

        $this->dispatch('highlightMarker', [
            'id' => $id,
            'type' => $type,
        ]);

        $this->dispatch('centerMap', [
            'lat' => $location['lat'],
            'lng' => $location['lng'],
            'zoom' => 12,
        ]);
    }

    #[Computed]
    public function selectedItemDetails()
    {
        if (! is_array($this->selectedItem)) {
            return null;
        }

        $id = isset($this->selectedItem['id']) && is_numeric($this->selectedItem['id'])
            ? (int) $this->selectedItem['id']
            : null;
        $type = is_string($this->selectedItem['type'] ?? null)
            ? $this->selectedItem['type']
            : '';

        if ($id === null || ! in_array($type, ['entity', 'federation'], true)) {
            return null;
        }

        $isVisibleInCurrentMap = collect($this->mapLocations)->contains(
            fn ($location) => $location['id'] === $id && $location['type'] === $type
        );

        if (! $isVisibleInCurrentMap) {
            return null;
        }

        $cacheKey = sprintf(
            'public_map_item_details_%s_%s_%s_%s_v3',
            $this->countryId() ?? 'none',
            $type,
            $id,
            (bool) config('public-map.show_contact_details', false) ? 'with_contacts' : 'without_contacts'
        );

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($id, $type) {
            $model = $type === 'federation'
                ? Federation::with([
                    'country',
                    'memberships' => function ($query) {
                        $query->select('id', 'federation_id', 'name', 'status_class')
                            ->with([
                                'plans' => function ($query) {
                                    $query->select('membership_plan.id', 'name', 'committee_id')
                                        ->with([
                                            'committee' => function ($query) {
                                                $query->select('id', 'name', 'code');
                                            },
                                        ]);
                                },
                            ]);
                    },
                ])
                    ->select($this->publicDetailsColumns())
                    ->where('country_id', $this->countryId())
                    ->whereNotNull(['lat', 'lng'])
                    ->find($id)
                : Entity::with([
                    'country',
                    'licenses' => fn ($query) => $query->where('status_class', ActiveLicenseAttributedState::class),
                    'licenses.license.committee',
                    'federations.country',
                ])
                    ->select($this->publicDetailsColumns())
                    ->where('visible_in_map', true)
                    ->where('country_id', $this->countryId())
                    ->whereHas('licenses', fn (Builder $query) => $query->where('status_class', ActiveLicenseAttributedState::class))
                    ->whereNotNull(['lat', 'lng'])
                    ->find($id);

            if ($model) {
                if (! (bool) config('public-map.show_contact_details', false)) {
                    $model->forceFill([
                        'email' => null,
                        'phone' => null,
                        'website' => null,
                    ]);
                }

                $model->logo_url = $model->getFirstMediaUrl('profile', 'thumb');
            }

            return $model;
        });
    }

    #[Computed]
    public function totalLocations(): int
    {
        if ($this->countryId() === null) {
            return 0;
        }

        $cacheKey = 'public_map_total_locations_' . md5(implode('|', $this->normalizedFilterState())) . '_v3';

        return Cache::remember(
            $cacheKey,
            now()->addMinutes(30),
            fn () => $this->federationsCount + $this->entitiesCount
        );
    }

    public function closeModal()
    {
        $this->selectedItem = null;
        $this->dispatch('modalClosed');
    }

    public function render()
    {
        return view('livewire.public.location-map.index')->layout('layouts.public', [
            'title' => __('location-map.title'),
            'currentPage' => 'map',
        ]);
    }

    private function configuredMapCenter(): array
    {
        $center = config('public-map.center', []);

        return [
            'lat' => (float) ($center['lat'] ?? 0),
            'lng' => (float) ($center['lng'] ?? 0),
            'zoom' => (int) ($center['zoom'] ?? 2),
        ];
    }

    private function countryId(): ?int
    {
        $countryId = config('public-map.country_id');

        return is_numeric($countryId) && (int) $countryId > 0
            ? (int) $countryId
            : null;
    }

    private function maxResults(): int
    {
        return max(1, min((int) config('public-map.max_results', 500), 1000));
    }

    private function committeeCode(string $key, string $default): string
    {
        $code = config("public-map.committee_codes.{$key}", $default);

        return is_string($code) && $code !== '' ? strtoupper($code) : $default;
    }

    private function normalizeFilters(): void
    {
        $this->selectedLicense = $this->integerFilterString($this->selectedLicense);
        $this->selectedSportLicense = $this->integerFilterString($this->selectedSportLicense);
        $this->selectedDivingLicense = $this->integerFilterString($this->selectedDivingLicense);
        $this->selectedDistrict = $this->integerFilterString($this->selectedDistrict);
        $this->searchTerm = $this->normalizedSearchTerm($this->searchTerm);
    }

    /**
     * @return array<int, string>
     */
    private function normalizedFilterState(): array
    {
        return [
            (string) ($this->countryId() ?? ''),
            $this->integerFilterString($this->selectedLicense),
            $this->integerFilterString($this->selectedSportLicense),
            $this->integerFilterString($this->selectedDivingLicense),
            $this->integerFilterString($this->selectedDistrict),
            $this->normalizedSearchTerm($this->searchTerm),
            (bool) config('public-map.include_federations', false) ? 'federations' : 'entities-only',
        ];
    }

    private function integerFilterString($value): string
    {
        if (! is_scalar($value)) {
            return '';
        }

        $value = trim((string) $value);

        return ctype_digit($value) ? (string) (int) $value : '';
    }

    private function integerFilterValue($value): ?int
    {
        $value = $this->integerFilterString($value);

        return $value === '' ? null : (int) $value;
    }

    private function normalizedSearchTerm($value): string
    {
        if (! is_scalar($value)) {
            return '';
        }

        return mb_substr(trim((string) $value), 0, 100);
    }

    /**
     * @return array<int, string>
     */
    private function publicDetailsColumns(): array
    {
        $columns = ['id', 'name', 'address', 'location', 'country_id', 'member_code'];

        if ((bool) config('public-map.show_contact_details', false)) {
            $columns = [...$columns, 'email', 'phone', 'website'];
        }

        return $columns;
    }
}
