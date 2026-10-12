<?php

namespace App\Support;

use App\Models\BasicSettings\Basic;
use App\Models\Booking;
use App\Models\CourtBlockedSlot;
use App\Models\Hotel;
use App\Models\Membership;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CourtDailyAvailability
{
    public static function cardData(Collection $rooms, ?string $requestedDate = null): array
    {
        $rooms = $rooms->unique('id')->values();
        $eligibility = self::eligibilityForRooms($rooms);
        $eligibleRooms = $rooms->filter(fn ($room) =>
            ($eligibility[$room->id]['status'] ?? null) === 'available'
        )->values();

        return [
            'eligibility' => $eligibility,
            'availability' => self::forRooms($eligibleRooms, $requestedDate),
        ];
    }

    public static function eligibilityForRooms(Collection $rooms): array
    {
        $settings = Basic::query()->select('timezone')->first();
        $timezone = $settings->timezone ?? config('app.timezone', 'UTC');
        $today = Carbon::now($timezone)->toDateString();
        $siteIds = $rooms->pluck('hotel_id')->filter()->unique()->values();
        $sites = Hotel::query()
            ->with('vendor:id,status')
            ->whereIn('id', $siteIds)
            ->get(['id', 'vendor_id', 'status', 'verification_status'])
            ->keyBy('id');
        $vendorIds = $sites->pluck('vendor_id')->filter()->unique()->values();
        $subscribedVendorIds = Membership::query()
            ->whereIn('vendor_id', $vendorIds)
            ->where('status', 1)
            ->where('start_date', '<=', $today)
            ->where('expire_date', '>=', $today)
            ->whereHas('package')
            ->pluck('vendor_id')
            ->unique()
            ->flip();

        return $rooms->mapWithKeys(function ($room) use ($sites, $subscribedVendorIds) {
            $site = $sites->get($room->hotel_id);
            $hasRegisteredVendor = $site
                && (int) $site->vendor_id !== 0
                && (int) $site->vendor_id === (int) $room->vendor_id
                && $site->vendor
                && (int) $site->vendor->status === 1;
            $isBookable = $site ? $site->isBookable() : false;
            $hasSubscription = $hasRegisteredVendor
                && $subscribedVendorIds->has((int) $site->vendor_id);

            return [$room->id => [
                'status' => self::resolveEligibilityStatus(
                    $hasRegisteredVendor,
                    $isBookable,
                    $hasSubscription
                ),
            ]];
        })->all();
    }

    public static function resolveEligibilityStatus(
        bool $hasRegisteredVendor,
        bool $isBookable,
        bool $hasSubscription
    ): string {
        if (!$hasRegisteredVendor) {
            return 'owner_registration_required';
        }

        if (!$isBookable) {
            return 'verification_required';
        }

        if (!$hasSubscription) {
            return 'subscription_required';
        }

        return 'available';
    }

    public static function forRooms(Collection $rooms, ?string $requestedDate = null): array
    {
        $settings = Basic::query()->select('timezone', 'time_format')->first();
        $timezone = $settings->timezone ?? config('app.timezone', 'UTC');
        $timeFormat = (int) ($settings->time_format ?? 12);
        $date = self::resolveDate($requestedDate, $timezone);
        $dayStart = $date->copy()->startOfDay();
        $dayEnd = $dayStart->copy()->addDay();
        $hasOvernightHours = $rooms->contains(fn ($room) =>
            $room->open_time && $room->close_time && $room->close_time < $room->open_time
        );
        $queryEnd = $hasOvernightHours ? $dayEnd->copy()->addDay() : $dayEnd;
        $roomIds = $rooms->pluck('id')->filter()->unique()->values();

        if ($roomIds->isEmpty()) {
            return [];
        }

        $bookings = Booking::query()
            ->whereIn('room_id', $roomIds)
            ->where('payment_status', '!=', 2)
            ->where('check_in_date_time', '<', $queryEnd->format('Y-m-d H:i:s'))
            ->where('check_out_date_time', '>', $dayStart->format('Y-m-d H:i:s'))
            ->get(['room_id', 'check_in_date_time', 'check_out_date_time'])
            ->groupBy('room_id');

        $blockedSlots = CourtBlockedSlot::query()
            ->whereIn('room_id', $roomIds)
            ->where('starts_at', '<', $queryEnd->format('Y-m-d H:i:s'))
            ->where('ends_at', '>', $dayStart->format('Y-m-d H:i:s'))
            ->get(['room_id', 'starts_at', 'ends_at'])
            ->groupBy('room_id');

        return $rooms->unique('id')->mapWithKeys(function ($room) use ($bookings, $blockedSlots, $date, $timezone, $timeFormat) {
            $intervals = collect($bookings->get($room->id, []))->map(fn ($booking) => [
                'start' => $booking->getRawOriginal('check_in_date_time'),
                'end' => $booking->getRawOriginal('check_out_date_time'),
            ])->merge(collect($blockedSlots->get($room->id, []))->map(fn ($slot) => [
                'start' => $slot->getRawOriginal('starts_at'),
                'end' => $slot->getRawOriginal('ends_at'),
            ]))->all();

            $schedule = self::buildSlots(
                $room->open_time,
                $room->close_time,
                $date->toDateString(),
                $intervals,
                $timeFormat,
                $timezone
            );

            return [$room->id => [
                'date' => $date->format('M d, Y'),
                'open' => $schedule['open'],
                'close' => $schedule['close'],
                'slots' => $schedule['slots'],
            ]];
        })->all();
    }

    public static function buildSlots(
        ?string $openTime,
        ?string $closeTime,
        string $date,
        array $intervals = [],
        int $timeFormat = 12,
        string $timezone = 'UTC'
    ): array {
        if (!$openTime || !$closeTime) {
            return ['open' => null, 'close' => null, 'slots' => []];
        }

        try {
            $open = Carbon::parse($date.' '.$openTime, $timezone);
            $close = Carbon::parse($date.' '.$closeTime, $timezone);
        } catch (\Throwable $exception) {
            return ['open' => null, 'close' => null, 'slots' => []];
        }

        if ($close->lessThanOrEqualTo($open)) {
            $close->addDay();
        }

        $normalizedIntervals = collect($intervals)->map(function ($interval) use ($timezone) {
            try {
                return [
                    'start' => Carbon::parse($interval['start'], $timezone),
                    'end' => Carbon::parse($interval['end'], $timezone),
                ];
            } catch (\Throwable $exception) {
                return null;
            }
        })->filter();

        $format = $timeFormat === 24 ? 'H:i' : 'g A';
        $slots = [];

        for ($cursor = $open->copy(); $cursor->lessThan($close); $cursor->addHour()) {
            $slotEnd = $cursor->copy()->addHour()->min($close);
            $occupied = $normalizedIntervals->contains(fn ($interval) =>
                $interval['start']->lessThan($slotEnd) && $interval['end']->greaterThan($cursor)
            );

            $slots[] = [
                'start' => $cursor->format($format),
                'end' => $slotEnd->format($format),
                'label' => $cursor->format($format).' - '.$slotEnd->format($format),
                'occupied' => $occupied,
            ];
        }

        return [
            'open' => $open->format($format),
            'close' => $close->format($format),
            'slots' => $slots,
        ];
    }

    private static function resolveDate(?string $requestedDate, string $timezone): Carbon
    {
        if ($requestedDate) {
            try {
                return Carbon::parse($requestedDate, $timezone)->startOfDay();
            } catch (\Throwable $exception) {
                // Fall through to the venue's current date.
            }
        }

        return Carbon::now($timezone)->startOfDay();
    }
}
