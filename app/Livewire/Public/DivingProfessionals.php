<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use Domain\Geographic\Models\District;
use Domain\Individuals\Models\Individual;
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

class DivingProfessionals extends Component
{
    use WithPagination;

    public string $searchName = '';

    public string $selectedDistrict = '';

    public string $selectedStatus = '';

    protected array $queryString = [
        'searchName' => ['except' => ''],
        'selectedDistrict' => ['except' => ''],
        'selectedStatus' => ['except' => ''],
    ];

    public function updatingSearchName(): void
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
        // Get Portugal's districts (country_id = 1 for Portugal typically)
        return District::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    #[Computed]
    public function statusOptions(): array
    {
        return [
            'active' => __('public.diving_professionals.status.active'),
            'expired' => __('public.diving_professionals.status.expired'),
            'suspended' => __('public.diving_professionals.status.suspended'),
        ];
    }

    #[Computed]
    public function professionals(): LengthAwarePaginator
    {
        return Individual::query()
            ->select(['id', 'name', 'surname', 'district_id'])
            ->where('visible_in_diving_professional_registry', true)
            ->whereHas('licenses', function (Builder $query) {
                $query->whereHas('license', function (Builder $q) {
                    $q->whereHas('committee', fn (Builder $c) => $c->where('code', 'DIVINGSERVICES'));
                })
                    ->whereIn('status_class', [
                        ActiveLicenseAttributedState::class,
                        ExpiredLicenseAttributedState::class,
                        SuspendedLicenseAttributedState::class,
                    ]);
            })
            // Filter by name (first or last)
            ->when($this->searchName, function (Builder $q) {
                $term = '%' . $this->searchName . '%';
                $q->where(function (Builder $query) use ($term) {
                    $query->where('name', 'like', $term)
                        ->orWhere('surname', 'like', $term);
                });
            })
            // Filter by district
            ->when($this->selectedDistrict, fn (Builder $q) => $q->where('district_id', $this->selectedDistrict))
            // Filter by license status
            ->when($this->selectedStatus, function (Builder $q) {
                $statusClass = $this->getStatusClass($this->selectedStatus);
                if ($statusClass) {
                    $q->whereHas('licenses', function (Builder $query) use ($statusClass) {
                        $query->whereHas('license.committee', fn (Builder $c) => $c->where('code', 'DIVINGSERVICES'))
                            ->where('status_class', $statusClass);
                    });
                }
            })
            // Eager load relationships
            ->with([
                'district',
                'licenses' => fn ($q) => $q->whereHas(
                    'license.committee',
                    fn (Builder $c) => $c->where('code', 'DIVINGSERVICES')
                )
                    ->whereIn('status_class', [
                        ActiveLicenseAttributedState::class,
                        ExpiredLicenseAttributedState::class,
                        SuspendedLicenseAttributedState::class,
                    ])
                    ->with('license.sport'),
            ])
            ->orderBy('name')
            ->orderBy('surname')
            ->paginate(12);
    }

    public function clearFilters(): void
    {
        $this->reset(['searchName', 'selectedDistrict', 'selectedStatus']);
        $this->resetPage();
    }

    public function getLicenseStatus(Individual $professional): string
    {
        $license = $professional->licenses->first();

        if (! $license) {
            return 'expired';
        }

        return match ($license->status_class) {
            ActiveLicenseAttributedState::class => 'active',
            ExpiredLicenseAttributedState::class => 'expired',
            SuspendedLicenseAttributedState::class => 'suspended',
            default => 'expired',
        };
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
        return view('livewire.public.diving-professionals.index')
            ->layout('layouts.public', [
                'title' => __('public.diving_professionals.title'),
                'currentPage' => 'diving-professionals',
            ]);
    }
}
