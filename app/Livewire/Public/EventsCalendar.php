<?php

declare(strict_types=1);

namespace App\Livewire\Public;

use App\Enums\EvtEventCategoryTypeEnum;
use App\Models\Sport;
use Carbon\Carbon;
use Domain\EvtEvents\Actions\PublicEventsQueryAction;
use Domain\EvtEvents\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class EventsCalendar extends Component
{
    use WithPagination;

    public string $view = 'list';

    public string $calendarMode = 'month';

    public string $sportId = '';

    public string $type = '';

    public bool $includePast = false;

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public int $calendarYear;

    public int $calendarMonth;

    protected array $queryString = [
        'view' => ['except' => 'list'],
        'calendarMode' => ['except' => 'month'],
        'sportId' => ['except' => ''],
        'type' => ['except' => ''],
        'includePast' => ['except' => false],
        'dateFrom' => ['except' => null],
        'dateTo' => ['except' => null],
        'calendarYear' => ['except' => null],
        'calendarMonth' => ['except' => null],
    ];

    public function mount(): void
    {
        $today = Carbon::today();
        $this->calendarYear = $this->calendarYear ?? (int) $today->year;
        $this->calendarMonth = $this->calendarMonth ?? (int) $today->month;
        $this->normalizePublicFilters();
    }

    public function updatingSportId(): void
    {
        $this->resetPage();
    }

    public function updatingType(): void
    {
        $this->resetPage();
    }

    public function updatingIncludePast(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function setView(string $view): void
    {
        $this->view = in_array($view, ['list', 'grid', 'calendar'], true) ? $view : 'list';
    }

    public function setCalendarMode(string $mode): void
    {
        $this->calendarMode = in_array($mode, ['month', 'year'], true) ? $mode : 'month';
    }

    public function previousPeriod(): void
    {
        if ($this->calendarMode === 'year') {
            $this->calendarYear -= 1;

            return;
        }

        $cursor = Carbon::create($this->calendarYear, $this->calendarMonth, 1)->subMonth();
        $this->calendarYear = (int) $cursor->year;
        $this->calendarMonth = (int) $cursor->month;
    }

    public function nextPeriod(): void
    {
        if ($this->calendarMode === 'year') {
            $this->calendarYear += 1;

            return;
        }

        $cursor = Carbon::create($this->calendarYear, $this->calendarMonth, 1)->addMonth();
        $this->calendarYear = (int) $cursor->year;
        $this->calendarMonth = (int) $cursor->month;
    }

    public function clearFilters(): void
    {
        $this->sportId = '';
        $this->type = '';
        $this->includePast = false;
        $this->dateFrom = null;
        $this->dateTo = null;
        $this->resetPage();
    }

    #[Computed]
    public function sportOptions(): Collection
    {
        return Sport::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Sport $sport) => [
                'id' => $sport->id,
                'label' => $sport->translated_name,
            ]);
    }

    #[Computed]
    public function typeOptions(): array
    {
        return [
            EvtEventCategoryTypeEnum::competition->value => __('public.events.types.competition'),
            EvtEventCategoryTypeEnum::organization->value => __('public.events.types.organization'),
        ];
    }

    #[Computed]
    public function events(): LengthAwarePaginator
    {
        return $this->buildBaseQuery()
            ->with('media')
            ->orderBy('start_date')
            ->paginate(12);
    }

    #[Computed]
    public function eventsByMonth(): Collection
    {
        return $this->events
            ->getCollection()
            ->groupBy(fn (Event $event): string => $event->start_date
                ? $event->start_date->format('Y-m')
                : '0000-00');
    }

    public function monthLabel(string $key): string
    {
        if ($key === '0000-00') {
            return __('public.events.list.tba');
        }

        [$year, $month] = explode('-', $key);

        return Carbon::create((int) $year, (int) $month, 1)
            ->locale(app()->getLocale())
            ->isoFormat('MMMM YYYY');
    }

    #[Computed]
    public function calendarEvents(): Collection
    {
        if ($this->calendarMode === 'year') {
            $windowStart = Carbon::create($this->calendarYear, 1, 1)->startOfYear();
            $windowEnd = $windowStart->copy()->endOfYear();
        } else {
            $monthStart = Carbon::create($this->calendarYear, $this->calendarMonth, 1)->startOfMonth();
            $windowStart = $monthStart->copy()->startOfWeek();
            $windowEnd = $monthStart->copy()->endOfMonth()->endOfWeek();
        }

        $filterStart = $this->parseDateFilter($this->dateFrom);
        $filterEnd = $this->parseDateFilter($this->dateTo, endOfDay: true);
        $effectiveStart = $filterStart && $filterStart->greaterThan($windowStart) ? $filterStart : $windowStart;
        $effectiveEnd = $filterEnd && $filterEnd->lessThan($windowEnd) ? $filterEnd : $windowEnd;

        if ($effectiveStart->greaterThan($effectiveEnd)) {
            return collect();
        }

        return app(PublicEventsQueryAction::class)
            ->execute(
                sportId: $this->sportId !== '' ? (int) $this->sportId : null,
                type: $this->type ?: null,
                includePast: $this->includePast,
                from: $effectiveStart,
                to: $effectiveEnd,
            )
            ->orderBy('start_date')
            ->get();
    }

    #[Computed]
    public function stats(): array
    {
        $base = $this->buildBaseQuery();

        $eventsCount = (clone $base)->count();

        $organizers = (clone $base)
            ->join('evt_organizers', 'evt_organizers.event_id', '=', 'evt_events.id')
            ->select('evt_organizers.organizable_type', 'evt_organizers.organizable_id')
            ->distinct()
            ->get()
            ->count();

        $eventDays = (clone $base)
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->get(['start_date', 'end_date'])
            ->sum(fn (Event $event) => $event->start_date->diffInDays($event->end_date) + 1);

        return [
            'events' => $eventsCount,
            'organizers' => $organizers,
            'event_days' => (int) $eventDays,
        ];
    }

    #[Computed]
    public function calendarPeriodLabel(): string
    {
        if ($this->calendarMode === 'year') {
            return (string) $this->calendarYear;
        }

        return Carbon::create($this->calendarYear, $this->calendarMonth, 1)
            ->locale(app()->getLocale())
            ->isoFormat('MMMM YYYY');
    }

    /**
     * @return array<int, array{date: Carbon, inMonth: bool, isToday: bool, events: Collection}>
     */
    public function calendarDays(Collection $events, ?int $year = null, ?int $month = null): array
    {
        $year ??= $this->calendarYear;
        $month ??= $this->calendarMonth;

        $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $cursor = $monthStart->copy()->startOfWeek();
        $end = $monthEnd->copy()->endOfWeek();
        $today = Carbon::today();

        $days = [];
        while ($cursor->lte($end)) {
            $day = $cursor->copy();
            $days[] = [
                'date' => $day,
                'inMonth' => $day->month === $month,
                'isToday' => $day->isSameDay($today),
                'events' => $events->filter(function (Event $event) use ($day): bool {
                    if (! $event->start_date || ! $event->end_date) {
                        return false;
                    }

                    return $day->betweenIncluded(
                        $event->start_date->copy()->startOfDay(),
                        $event->end_date->copy()->endOfDay(),
                    );
                })->values(),
            ];
            $cursor->addDay();
        }

        return $days;
    }

    /**
     * @return array<int, array{month: int, label: string, days: array}>
     */
    public function yearMonths(Collection $events): array
    {
        $months = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthDate = Carbon::create($this->calendarYear, $month, 1);
            $months[] = [
                'month' => $month,
                'label' => $monthDate->locale(app()->getLocale())->isoFormat('MMMM'),
                'days' => $this->calendarDays($events, $this->calendarYear, $month),
            ];
        }

        return $months;
    }

    public function focusMonth(int $month): void
    {
        $this->calendarMonth = max(1, min(12, $month));
        $this->calendarMode = 'month';
    }

    public function render(): View
    {
        return view('livewire.public.events-calendar.index')
            ->layout('layouts.public', [
                'title' => __('public.events.title'),
                'currentPage' => 'events',
            ]);
    }

    protected function buildBaseQuery()
    {
        return app(PublicEventsQueryAction::class)->execute(
            sportId: $this->sportId !== '' ? (int) $this->sportId : null,
            type: $this->type ?: null,
            includePast: $this->includePast,
            from: $this->parseDateFilter($this->dateFrom),
            to: $this->parseDateFilter($this->dateTo, endOfDay: true),
        );
    }

    private function normalizePublicFilters(): void
    {
        $this->view = in_array($this->view, ['list', 'grid', 'calendar'], true) ? $this->view : 'list';
        $this->calendarMode = in_array($this->calendarMode, ['month', 'year'], true) ? $this->calendarMode : 'month';
        $this->sportId = ctype_digit($this->sportId) ? (string) (int) $this->sportId : '';
        $this->type = EvtEventCategoryTypeEnum::tryFrom($this->type)?->value ?? '';
        $this->dateFrom = $this->normalizeDateFilter($this->dateFrom);
        $this->dateTo = $this->normalizeDateFilter($this->dateTo);
        $this->calendarYear = max(1900, min(2200, $this->calendarYear));
        $this->calendarMonth = max(1, min(12, $this->calendarMonth));
    }

    private function parseDateFilter(?string $value, bool $endOfDay = false): ?Carbon
    {
        $value = $this->normalizeDateFilter($value);

        if ($value === null) {
            return null;
        }

        $date = Carbon::createFromFormat('Y-m-d', $value);

        return $endOfDay ? $date->endOfDay() : $date->startOfDay();
    }

    private function normalizeDateFilter(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $value = trim($value);
        try {
            $date = Carbon::createFromFormat('Y-m-d', $value);
        } catch (\Throwable) {
            return null;
        }

        if (! $date) {
            return null;
        }

        $errors = Carbon::getLastErrors() ?: ['warning_count' => 0, 'error_count' => 0];

        if ($errors['warning_count'] > 0 || $errors['error_count'] > 0) {
            return null;
        }

        return $date->toDateString() === $value ? $value : null;
    }
}
