<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Diagnostics;

use Domain\Diagnostics\Actions\DiagnoseAthleteEligibilityAction;
use Domain\Diagnostics\Actions\DiagnoseCoachEligibilityAction;
use Domain\Diagnostics\Actions\DiagnoseOfficialEligibilityAction;
use Domain\Diagnostics\Actions\DiagnoseRefereeEligibilityAction;
use Domain\Diagnostics\Actions\GenerateIndividualProfileDiagnosticAction;
use Domain\EvtEvents\Models\Competition;
use Domain\EvtEvents\Models\Event;
use Domain\Federations\Models\Federation;
use Domain\Individuals\Models\Individual;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class EligibilityDiagnosticCenter extends Component
{
    public string $activeTab = 'individual';

    // Individual Profile Tab
    public string $individualSearch = '';
    public ?string $selectedIndividualId = null;

    /** @var array|null Stored as array to avoid Livewire serialization issues */
    public ?array $profileDiagnostic = null;

    // Event Enrollment Tab
    public ?int $selectedEventId = null;
    public ?int $selectedCompetitionId = null;
    public string $selectedRole = 'athlete';
    public string $eventIndividualSearch = '';
    public ?string $eventSelectedIndividualId = null;

    /** @var array|null Stored as array to avoid Livewire serialization issues */
    public ?array $eventDiagnosticResult = null;

    // Federation scoping (for federation admins)
    protected ?Federation $scopedFederation = null;

    public function mount(): void
    {
        $this->scopeFederationAccess();
    }

    protected function scopeFederationAccess(): void
    {
        $user = Auth::user();
        $group = $user->group()->first();

        if ($group && $group['code'] === 'FEDERATION') {
            $this->scopedFederation = $user->federations()->first();
        }
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetErrorBag();
    }

    // Individual Profile Tab Methods
    #[Computed]
    public function searchResults(): Collection
    {
        return $this->searchIndividuals($this->individualSearch);
    }

    public function selectIndividual(string $id): void
    {
        $this->selectedIndividualId = $id;
        $this->individualSearch = '';
        $this->runProfileDiagnostic();
    }

    public function runProfileDiagnostic(): void
    {
        if (! $this->selectedIndividualId) {
            return;
        }

        $individual = Individual::find($this->selectedIndividualId);
        if (! $individual) {
            return;
        }

        $action = app(GenerateIndividualProfileDiagnosticAction::class);
        $result = $action->execute($individual);
        $this->profileDiagnostic = $result->toArray();
    }

    public function clearIndividualSelection(): void
    {
        $this->selectedIndividualId = null;
        $this->profileDiagnostic = null;
        $this->individualSearch = '';
    }

    // Event Enrollment Tab Methods
    #[Computed]
    public function availableEvents(): Collection
    {
        $query = Event::query()
            ->with(['sport', 'organizer'])
            ->orderBy('start_date', 'desc')
            ->limit(50);

        if ($this->scopedFederation) {
            $query->whereHas('organizer', function ($q) {
                $q->where('organizable_type', Federation::class)
                    ->where('organizable_id', $this->scopedFederation->id);
            });
        }

        return $query->get();
    }

    #[Computed]
    public function availableCompetitions(): Collection
    {
        if (! $this->selectedEventId) {
            return collect();
        }

        return Competition::where('event_id', $this->selectedEventId)
            ->with(['disciplines'])
            ->get();
    }

    #[Computed]
    public function eventSearchResults(): Collection
    {
        return $this->searchIndividuals($this->eventIndividualSearch);
    }

    public function selectEventIndividual(string $id): void
    {
        $this->eventSelectedIndividualId = $id;
        $this->eventIndividualSearch = '';
    }

    public function runEventDiagnostic(): void
    {
        if (! $this->selectedEventId || ! $this->eventSelectedIndividualId) {
            return;
        }

        $individual = Individual::find($this->eventSelectedIndividualId);
        $event = Event::find($this->selectedEventId);

        if (! $individual || ! $event) {
            return;
        }

        $actionClass = match ($this->selectedRole) {
            'athlete' => DiagnoseAthleteEligibilityAction::class,
            'coach' => DiagnoseCoachEligibilityAction::class,
            'referee' => DiagnoseRefereeEligibilityAction::class,
            'official' => DiagnoseOfficialEligibilityAction::class,
            default => null,
        };

        if (! $actionClass) {
            return;
        }

        $competition = $this->selectedCompetitionId
            ? Competition::find($this->selectedCompetitionId)
            : null;

        $result = app($actionClass)->execute($individual, $event, $competition);
        $this->eventDiagnosticResult = $result->toArray();
    }

    public function clearEventDiagnostic(): void
    {
        $this->eventSelectedIndividualId = null;
        $this->eventDiagnosticResult = null;
        $this->eventIndividualSearch = '';
    }

    public function updatedSelectedEventId(): void
    {
        $this->selectedCompetitionId = null;
        $this->eventDiagnosticResult = null;
    }

    protected function searchIndividuals(string $search): Collection
    {
        if (strlen($search) < 2) {
            return collect();
        }

        $query = Individual::query()
            ->where(function ($q) use ($search) {
                $q->where('member_code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereRaw("CONCAT(name, ' ', surname) LIKE ?", ["%{$search}%"]);
            });

        if ($this->scopedFederation) {
            $query->whereHas('federations', fn ($q) => $q->where('federations.id', $this->scopedFederation->id));
        }

        return $query->limit(10)->get();
    }

    public function render(): View
    {
        return view('livewire.admin.diagnostics.eligibility-diagnostic-center');
    }
}
