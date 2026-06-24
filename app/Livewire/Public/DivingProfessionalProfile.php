<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use Domain\Individuals\Models\Individual;
use Domain\Licenses\Models\LicenseAttributed;
use Domain\Licenses\States\ActiveLicenseAttributedState;
use Domain\Licenses\States\ExpiredLicenseAttributedState;
use Domain\Licenses\States\SuspendedLicenseAttributedState;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class DivingProfessionalProfile extends Component
{
    public Individual $individual;

    public function mount(Individual $individual): void
    {
        if (! $individual->visible_in_diving_professional_registry) {
            abort(404);
        }

        $individual->load([
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
        ]);

        $this->individual = $individual;
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

    public function render(): View
    {
        return view('livewire.public.diving-professionals.show')
            ->layout('layouts.public', [
                'title' => $this->individual->name . ' ' . $this->individual->surname,
                'currentPage' => 'diving-professionals',
            ]);
    }
}
