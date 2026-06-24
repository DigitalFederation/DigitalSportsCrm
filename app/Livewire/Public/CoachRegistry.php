<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use Domain\Certifications\Models\CertificationAttributed;
use Domain\Certifications\States\ActiveCertificationAttributedState;
use Domain\Certifications\States\ExpiredCertificationAttributedState;
use Domain\Certifications\States\SuspendedCertificationAttributedState;
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

class CoachRegistry extends Component
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
        return District::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    #[Computed]
    public function statusOptions(): array
    {
        return [
            'active' => __('public.coach_registry.status.active'),
            'expired' => __('public.coach_registry.status.expired'),
            'suspended' => __('public.coach_registry.status.suspended'),
        ];
    }

    #[Computed]
    public function professionals(): LengthAwarePaginator
    {
        $validStatuses = $this->selectedStatus
            ? array_filter([$this->getStatusClass($this->selectedStatus)])
            : [
                ActiveCertificationAttributedState::class,
                ExpiredCertificationAttributedState::class,
                SuspendedCertificationAttributedState::class,
            ];

        $certScope = function ($query) use ($validStatuses): void {
            $query->whereHas('certification', function (Builder $q) {
                $q->whereHas('committee', fn (Builder $c) => $c->where('code', 'SPORT'))
                    ->whereHas('professionalRole', fn (Builder $pr) => $pr->where('role', 'COACH'));
            })
                ->whereIn('status_class', $validStatuses);
        };

        return Individual::query()
            ->select(['id', 'name', 'surname', 'district_id'])
            ->where('visible_in_coach_registry', true)
            ->whereHas('certificationsAttributed', $certScope)
            ->when($this->searchName, function (Builder $q) {
                $term = '%' . $this->searchName . '%';
                $q->where(function (Builder $query) use ($term) {
                    $query->where('name', 'like', $term)
                        ->orWhere('surname', 'like', $term);
                });
            })
            ->when($this->selectedDistrict, fn (Builder $q) => $q->where('district_id', $this->selectedDistrict))
            ->with([
                'district',
                'media',
                'certificationsAttributed' => function ($q) use ($certScope) {
                    $certScope($q);
                    $q->with('certification.professionalRole')
                        ->latest('current_term_starts_at')
                        ->limit(1);
                },
                'licenses' => fn ($q) => $q->whereHas('license', function (Builder $lq) {
                    $lq->whereHas('committee', fn (Builder $c) => $c->where('code', 'SPORT'))
                        ->whereHas('professionalRole', fn (Builder $pr) => $pr->where('role', 'COACH'));
                })
                    ->whereIn('status_class', [
                        ActiveLicenseAttributedState::class,
                        ExpiredLicenseAttributedState::class,
                        SuspendedLicenseAttributedState::class,
                    ])
                    ->latest('created_at')
                    ->limit(1),
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

    public function getCertificationStatus(CertificationAttributed $certificationAttributed): string
    {
        return match ($certificationAttributed->status_class) {
            ActiveCertificationAttributedState::class => 'active',
            ExpiredCertificationAttributedState::class => 'expired',
            SuspendedCertificationAttributedState::class => 'suspended',
            default => 'expired',
        };
    }

    public function getLicenseStatus(LicenseAttributed $license): string
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
            'active' => ActiveCertificationAttributedState::class,
            'expired' => ExpiredCertificationAttributedState::class,
            'suspended' => SuspendedCertificationAttributedState::class,
            default => null,
        };
    }

    public function render(): View
    {
        return view('livewire.public.coach-registry.index')
            ->layout('layouts.public', [
                'title' => __('public.coach_registry.title'),
                'currentPage' => 'coach-registry',
            ]);
    }
}
