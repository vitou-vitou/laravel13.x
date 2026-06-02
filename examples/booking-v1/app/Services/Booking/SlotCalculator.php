<?php

namespace App\Services\Booking;

use App\Enums\BookingStatus;
use App\Models\AvailabilityRule;
use App\Models\BookableResource;
use App\Models\Booking;
use App\Models\Service;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class SlotCalculator
{
    /**
     * @return Collection<int, CarbonInterface>
     */
    public function availableStarts(Service $service, BookableResource $resource, CarbonInterface $from, CarbonInterface $to): Collection
    {
        $rules = $resource->availabilityRules()->get();
        $duration = $service->duration_minutes;
        $slots = collect();

        for ($day = $from->copy()->startOfDay(); $day->lte($to); $day->addDay()) {
            $dow = (int) $day->dayOfWeek;
            foreach ($rules->where('day_of_week', $dow) as $rule) {
                $slots = $slots->merge($this->slotsForRule($day, $rule, $duration, $from, $to));
            }
        }

        $blocked = $this->blockedIntervals($resource, $from, $to);

        return $slots
            ->unique(fn (CarbonInterface $s) => $s->toIso8601String())
            ->filter(fn (CarbonInterface $start) => $start->gte($from) && $start->lte($to))
            ->filter(fn (CarbonInterface $start) => ! $this->overlapsBlocked($start, $start->copy()->addMinutes($duration), $blocked))
            ->sort()
            ->values();
    }

    /**
     * @return Collection<int, CarbonInterface>
     */
    private function slotsForRule(
        CarbonInterface $day,
        AvailabilityRule $rule,
        int $durationMinutes,
        CarbonInterface $from,
        CarbonInterface $to,
    ): Collection {
        $start = Carbon::parse($day->toDateString().' '.$this->timeString($rule->start_time));
        $end = Carbon::parse($day->toDateString().' '.$this->timeString($rule->end_time));
        $slots = collect();
        $cursor = $start->copy();

        while ($cursor->copy()->addMinutes($durationMinutes)->lte($end)) {
            if ($cursor->gte($from) && $cursor->lte($to)) {
                $slots->push($cursor->copy());
            }
            $cursor->addMinutes($durationMinutes);
        }

        return $slots;
    }

    private function timeString(mixed $value): string
    {
        if ($value instanceof CarbonInterface) {
            return $value->format('H:i:s');
        }

        return (string) $value;
    }

    /**
     * @return Collection<int, array{0: CarbonInterface, 1: CarbonInterface}>
     */
    private function blockedIntervals(BookableResource $resource, CarbonInterface $from, CarbonInterface $to): Collection
    {
        return Booking::query()
            ->where('bookable_resource_id', $resource->id)
            ->where(function ($q) use ($from, $to) {
                $q->where('starts_at', '<', $to)->where('ends_at', '>', $from);
            })
            ->where(function ($q) {
                $q->where('status', BookingStatus::Confirmed)
                    ->orWhere(function ($q2) {
                        $q2->where('status', BookingStatus::Held)
                            ->where('hold_expires_at', '>', now());
                    });
            })
            ->get()
            ->map(fn (Booking $b) => [$b->starts_at, $b->ends_at]);
    }

    /**
     * @param  Collection<int, array{0: CarbonInterface, 1: CarbonInterface}>  $blocked
     */
    private function overlapsBlocked(CarbonInterface $start, CarbonInterface $end, Collection $blocked): bool
    {
        foreach ($blocked as [$bStart, $bEnd]) {
            if ($start->lt($bEnd) && $end->gt($bStart)) {
                return true;
            }
        }

        return false;
    }
}
