<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Helpers\VendorPermissionHelper;
use App\Models\Booking;
use App\Models\BasicSettings\Basic;
use App\Models\CourtBlockedSlot;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CourtDisplayController extends Controller
{
    private const PACKAGE_FEATURE = 'Live Court Display';

    public function index()
    {
        $this->authorizeFeature();

        return view('vendors.court-display.index');
    }

    public function data()
    {
        $vendorId = $this->authorizeFeature();
        $openPlayEnabled = VendorPermissionHelper::hasPackageFeature($vendorId, 'Open Play');
        $timezone = optional(Basic::first())->timezone ?: config('app.timezone', 'UTC');
        $now = Carbon::now($timezone);
        // Send a wider wall-clock window and let the display device classify
        // active/upcoming items using the same system clock shown on screen.
        $windowStart = $now->copy()->subDay()->startOfDay();
        $windowEnd = $now->copy()->addDays(2)->endOfDay();

        $courts = Room::with('room_content')
            ->where('vendor_id', $vendorId)
            ->where('status', 1)
            ->orderBy('id')
            ->get();

        $bookings = Booking::where('vendor_id', $vendorId)
            ->whereNull('open_play_slot_id')
            ->where('payment_status', '!=', 2)
            ->where('check_out_date_time', '>', $windowStart->format('Y-m-d H:i:s'))
            ->where('check_in_date_time', '<=', $windowEnd->format('Y-m-d H:i:s'))
            ->orderBy('check_in_date_time')
            ->get()
            ->groupBy('room_id');

        $scheduleSlots = CourtBlockedSlot::where('vendor_id', $vendorId)
            ->where('ends_at', '>', $windowStart)
            ->where('starts_at', '<=', $windowEnd)
            ->orderBy('starts_at')
            ->get()
            ->groupBy('room_id');

        $activeCount = 0;
        $upcomingCount = 0;

        $courtData = $courts->map(function ($court) use ($bookings, $scheduleSlots, $now, $timezone, $openPlayEnabled, &$activeCount, &$upcomingCount) {
            $events = collect();

            foreach ($bookings->get($court->id, collect()) as $booking) {
                $events->push([
                    'type' => 'rental',
                    'label' => $booking->booking_name ?: __('Reserved'),
                    'starts_at' => Carbon::parse($booking->check_in_date_time, $timezone),
                    'ends_at' => Carbon::parse($booking->check_out_date_time, $timezone),
                ]);
            }

            foreach ($scheduleSlots->get($court->id, collect()) as $slot) {
                $isOpenPlay = $slot->slot_type === 'open_play' && $openPlayEnabled;
                $events->push([
                    'type' => $isOpenPlay ? 'open_play' : 'blocked',
                    'label' => $isOpenPlay
                        ? ($slot->title ?: __('Open Play'))
                        : ($slot->reason ?: __('Court Blocked')),
                    'starts_at' => Carbon::parse($slot->getRawOriginal('starts_at'), $timezone),
                    'ends_at' => Carbon::parse($slot->getRawOriginal('ends_at'), $timezone),
                ]);
            }

            $events = $events->sortBy('starts_at')->values();
            $active = $events->first(fn ($event) => $event['starts_at']->lte($now) && $event['ends_at']->gt($now));
            $upcoming = $events
                ->filter(fn ($event) => $event['starts_at']->gt($now))
                ->take(3)
                ->values();

            if ($active) {
                $activeCount++;
            }
            $upcomingCount += $upcoming->count();

            $serialize = fn ($event) => [
                'type' => $event['type'],
                'label' => $event['label'],
                // Keep venue wall-clock values consistent with the booking calendar.
                'starts_at' => $event['starts_at']->format('Y-m-d\TH:i:s'),
                'ends_at' => $event['ends_at']->format('Y-m-d\TH:i:s'),
                'starts_at_timestamp' => $event['starts_at']->getTimestampMs(),
                'ends_at_timestamp' => $event['ends_at']->getTimestampMs(),
                'start_label' => $event['starts_at']->format('h:i A'),
                'end_label' => $event['ends_at']->format('h:i A'),
            ];

            return [
                'id' => $court->id,
                'name' => optional($court->room_content->first())->title ?: __('Court') . ' #' . $court->id,
                'active' => $active ? $serialize($active) : null,
                'upcoming' => $upcoming->map($serialize)->all(),
                'events' => $events->map($serialize)->all(),
            ];
        })->values();

        return response()->json([
            'server_time' => $now->format('Y-m-d\TH:i:s'),
            'timezone' => $timezone,
            'active_count' => $activeCount,
            'available_count' => max($courts->count() - $activeCount, 0),
            'upcoming_count' => $upcomingCount,
            'courts' => $courtData,
        ]);
    }

    private function authorizeFeature(): int
    {
        $vendorId = (int) Auth::guard('vendor')->id();
        abort_unless(VendorPermissionHelper::hasPackageFeature($vendorId, self::PACKAGE_FEATURE), 403, __('Your current package does not include Live Court Display.'));

        return $vendorId;
    }
}
