<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Models\Sport;
use Domain\Entities\Models\Entity;
use Domain\Geographic\Models\District;
use Domain\Licenses\Models\License;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\ExpiredLicenseAttributedState;
use Domain\Licenses\States\SuspendedLicenseAttributedState;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class ClubRegistry extends Component
{
    use WithPagination;

    public string $searchName = '';

    public string $selectedSportLicense = '';

    public string $selectedDistrict = '';

    public string $selectedStatus = '';

    protected array $queryString = [
        'searchName' => ['except' => ''],
        'selectedSportLicense' => ['except' => ''],
        'selectedDistrict' => ['except' => ''],
        'selectedStatus' => ['except' => ''],
    ];

    public function updatingSearchName(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedSportLicense(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedDistrict(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedStatus(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function districts(): Collection
    {
        return District::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    #[Computed]
    public function sports(): Collection
    {
        return Sport::query()
            ->orderBy('id')
            ->get(['id', 'name']);
    }

    #[Computed]
    public function sportLicenses(): Collection
    {
        return License::query()
            ->whereHas('committee', fn (Builder $c) => $c->where('code', 'SPORT'))
            ->whereHas('type', fn (Builder $t) => $t->where('name', 'entity'))
            ->whereHas('licensesAttributed', fn (Builder $q) => $q->whereIn('status_class', [
                ActiveLicenseAttributedState::class,
                ExpiredLicenseAttributedState::class,
                SuspendedLicenseAttributedState::class,
            ]))
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function statusOptions(): array
    {
        return [
            'active' => __('public.club_registry.status.active'),
            'expired' => __('public.club_registry.status.expired'),
            'suspended' => __('public.club_registry.status.suspended'),
        ];
    }

    #[Computed]
    public function entities(): LengthAwarePaginator
    {
        return Entity::query()
            ->where('visible_in_club_registry', true)
            ->select(['id', 'name', 'location', 'district_id'])
            ->where(function (Builder $q) {
                $q->whereHas('licenses', function (Builder $query) {
                    $query->whereHas('license', function (Builder $lq) {
                        $lq->whereHas('committee', fn (Builder $c) => $c->where('code', 'SPORT'))
                            ->whereHas('type', fn (Builder $t) => $t->where('name', 'entity'));
                    })
                        ->whereIn('status_class', [
                            ActiveLicenseAttributedState::class,
                            ExpiredLicenseAttributedState::class,
                            SuspendedLicenseAttributedState::class,
                        ]);
                })
                    ->orWhereHas('affiliations', fn ($a) => Entity::applyValidationPlanCondition($a));
            })
            ->when($this->searchName, function (Builder $q) {
                $term = '%' . $this->searchName . '%';
                $q->where('name', 'like', $term);
            })
            ->when($this->selectedSportLicense, function (Builder $q) {
                $q->whereHas('licenses', function (Builder $query) {
                    $query->where('license_id', $this->selectedSportLicense)
                        ->whereIn('status_class', [
                            ActiveLicenseAttributedState::class,
                            ExpiredLicenseAttributedState::class,
                            SuspendedLicenseAttributedState::class,
                        ]);
                });
            })
            ->when($this->selectedDistrict, fn (Builder $q) => $q->where('district_id', $this->selectedDistrict))
            ->when($this->selectedStatus, function (Builder $q) {
                $statusClass = $this->getStatusClass($this->selectedStatus);
                if ($statusClass) {
                    $q->whereHas('licenses', function (Builder $query) use ($statusClass) {
                        $query->whereHas('license', function (Builder $lq) {
                            $lq->whereHas('committee', fn (Builder $c) => $c->where('code', 'SPORT'))
                                ->whereHas('type', fn (Builder $t) => $t->where('name', 'entity'));
                        })
                            ->where('status_class', $statusClass);
                    });
                }
            })
            ->with([
                'district',
                'media',
                'licenses' => fn ($q) => $q->whereHas('license', function (Builder $lq) {
                    $lq->whereHas('committee', fn (Builder $c) => $c->where('code', 'SPORT'))
                        ->whereHas('type', fn (Builder $t) => $t->where('name', 'entity'));
                })
                    ->whereIn('status_class', [
                        ActiveLicenseAttributedState::class,
                        ExpiredLicenseAttributedState::class,
                        SuspendedLicenseAttributedState::class,
                    ])
                    ->with('license.sport'),
            ])
            ->orderBy('name')
            ->paginate(25);
    }

    public function clearFilters(): void
    {
        $this->reset(['searchName', 'selectedSportLicense', 'selectedDistrict', 'selectedStatus']);
        $this->resetPage();
    }

    public function getLicenseStatusForAttributed(LicenseAttributed $license): string
    {
        return match ($license->status_class) {
            ActiveLicenseAttributedState::class => 'active',
            ExpiredLicenseAttributedState::class => 'expired',
            SuspendedLicenseAttributedState::class => 'suspended',
            default => 'expired',
        };
    }

    public function getSportLicenseStatus(Entity $entity, int $sportId): ?string
    {
        $priority = ['active' => 3, 'suspended' => 2, 'expired' => 1];
        $best = null;

        foreach ($entity->licenses as $license) {
            if ($license->license?->sport_id !== $sportId) {
                continue;
            }

            $status = $this->getLicenseStatusForAttributed($license);

            if ($best === null || $priority[$status] > $priority[$best]) {
                $best = $status;
            }
        }

        return $best;
    }

    protected function getStatusClass(string $status): ?string
    {
        return match ($status) {
            'active' => ActiveLicenseAttributedState::class,
            'expired' => ExpiredLicenseAttributedState::class,
            'suspended' => SuspendedLicenseAttributedState::class,
            default => null,
        };
    }

    public function render(): View
    {
        return view('livewire.public.club-registry.index')
            ->layout('layouts.public', [
                'title' => __('public.club_registry.title'),
                'currentPage' => 'club-registry',
            ]);
    }
}
