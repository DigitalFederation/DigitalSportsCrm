<?php

declare(strict_types=1);

namespace Domain\EvtEvents\Actions;

use App\Enums\EvtEventCategoryTypeEnum;
use Carbon\Carbon;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\States\ArchiveEventState;
use Domain\EvtEvents\States\CanceledEventState;
use Illuminate\Database\Eloquent\Builder;

class PublicEventsQueryAction
{
    public function execute(
        ?int $sportId = null,
        ?string $type = null,
        bool $includePast = false,
        ?Carbon $from = null,
        ?Carbon $to = null,
    ): Builder {
        $query = Event::query()
            ->where('is_visible', true)
            ->whereNotIn('status_class', [
                ArchiveEventState::class,
                CanceledEventState::class,
            ])
            ->with(['competition.sport', 'venueCountry']);

        $eventCategory = $type !== null ? EvtEventCategoryTypeEnum::tryFrom($type) : null;

        if ($eventCategory !== null) {
            $query->where('event_category', $eventCategory->value);
        }

        if ($sportId !== null) {
            $query->whereHas('competition', fn (Builder $q) => $q->where('sport_id', $sportId));
        }

        if (! $includePast) {
            $query->where('end_date', '>=', Carbon::today());
        }

        if ($from !== null) {
            $query->where('end_date', '>=', $from->copy()->startOfDay());
        }

        if ($to !== null) {
            $query->where('start_date', '<=', $to->copy()->endOfDay());
        }

        return $query;
    }
}
