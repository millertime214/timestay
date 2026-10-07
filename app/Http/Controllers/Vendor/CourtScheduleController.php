<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Helpers\VendorPermissionHelper;
use App\Models\Booking;
use App\Models\CourtBlockedSlot;
use App\Models\Language;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CourtScheduleController extends Controller
{
    public function index(Request $request)
    {
        $vendorId = Auth::guard('vendor')->id();
        $canManageOpenPlay = VendorPermissionHelper::hasPackageFeature($vendorId, 'Open Play');
        $language = Language::where('code', $request->input('language'))->first()
            ?: Language::where('is_default', 1)->first();

        $courts = Room::with(['room_content' => function ($query) use ($language) {
            if ($language) {
                $query->where('language_id', $language->id);
            }
        }])->where('vendor_id', $vendorId)->where('status', 1)->get();

        $slots = CourtBlockedSlot::with('court.room_content')
            ->where('vendor_id', $vendorId)
            ->where('ends_at', '>=', now()->startOfDay())
            ->orderBy('starts_at')
            ->get();

        $blocks = $slots->where('slot_type', 'blocked');
        $openPlaySlots = $slots->where('slot_type', 'open_play');
        $currencyInfo = $this->getCurrencyInfo();

        return view('vendors.room-booking.schedule', compact(
            'courts',
            'blocks',
            'openPlaySlots',
            'language',
            'currencyInfo',
            'canManageOpenPlay'
        ));
    }

    public function showOpenPlay(int $id)
    {
        $this->authorizeOpenPlay();
        $slot = CourtBlockedSlot::with(['court.room_content', 'registrations.user'])
            ->where('vendor_id', Auth::guard('vendor')->id())
            ->where('slot_type', 'open_play')
            ->findOrFail($id);

        $language = Language::where('code', request()->input('language'))->first()
            ?: Language::where('is_default', 1)->first();

        return view('vendors.room-booking.open-play-details', compact('slot', 'language'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'room_id' => ['required', 'integer'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $vendorId = Auth::guard('vendor')->id();
        $court = Room::where('vendor_id', $vendorId)->findOrFail($data['room_id']);
        $startsAt = Carbon::parse($data['starts_at']);
        $endsAt = Carbon::parse($data['ends_at']);

        $bookingExists = Booking::where('room_id', $court->id)
            ->where('payment_status', '!=', 2)
            ->where('check_in_date_time', '<', $endsAt)
            ->where('check_out_date_time', '>', $startsAt)
            ->exists();

        $blockExists = CourtBlockedSlot::overlapping($court->id, $startsAt, $endsAt)->exists();

        if ($bookingExists || $blockExists) {
            return back()->withErrors(['starts_at' => __('This time range overlaps an existing booking or block.')])->withInput();
        }

        CourtBlockedSlot::create([
            'vendor_id' => $vendorId,
            'room_id' => $court->id,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'reason' => $data['reason'] ?? null,
            'slot_type' => 'blocked',
        ]);

        Session::flash('success', __('Court time blocked successfully.'));
        return redirect()->route('vendor.court_schedule.index');
    }

    public function storeOpenPlay(Request $request)
    {
        $this->authorizeOpenPlay();
        $data = array_merge([
            'title' => __('Open Play'),
            'category' => null,
        ], $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'room_id' => ['required', 'integer'],
            'starts_at' => ['required', 'date', 'after_or_equal:now'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'price_per_player' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'max_players' => ['required', 'integer', 'min:1', 'max:1000'],
            'poster' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]));

        $vendorId = Auth::guard('vendor')->id();
        $court = Room::where('vendor_id', $vendorId)->findOrFail($data['room_id']);
        $startsAt = Carbon::parse($data['starts_at']);
        $endsAt = Carbon::parse($data['ends_at']);

        $bookingExists = Booking::where('room_id', $court->id)
            ->where('payment_status', '!=', 2)
            ->where('check_in_date_time', '<', $endsAt)
            ->where('check_out_date_time', '>', $startsAt)
            ->exists();

        if ($bookingExists || CourtBlockedSlot::overlapping($court->id, $startsAt, $endsAt)->exists()) {
            return back()->withErrors([
                'open_play_starts_at' => __('This time range overlaps an existing booking, block, or Open Play slot.'),
            ])->withInput();
        }

        $posterName = null;
        if ($request->hasFile('poster')) {
            $poster = $request->file('poster');
            $posterName = uniqid('open-play-', true) . '.' . $poster->getClientOriginalExtension();
            $posterDirectory = public_path('assets/img/open-play');
            if (!is_dir($posterDirectory)) {
                mkdir($posterDirectory, 0775, true);
            }
            $poster->move($posterDirectory, $posterName);
        }

        CourtBlockedSlot::create([
            'vendor_id' => $vendorId,
            'room_id' => $court->id,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'reason' => $data['reason'] ?? null,
            'slot_type' => 'open_play',
            'title' => $data['title'],
            'category' => $data['category'],
            'price_per_player' => $data['price_per_player'],
            'max_players' => $data['max_players'],
            'poster' => $posterName,
        ]);

        Session::flash('success', __('Open Play hours added successfully.'));

        return redirect()->route('vendor.court_schedule.index');
    }

    public function destroy(int $id)
    {
        $slot = CourtBlockedSlot::where('vendor_id', Auth::guard('vendor')->id())->findOrFail($id);
        if ($slot->slot_type === 'open_play') {
            $this->authorizeOpenPlay();
        }
        if ($slot->slot_type === 'open_play' && Booking::where('open_play_slot_id', $slot->id)->exists()) {
            return back()->with('warning', __('This Open Play session already has registrations and cannot be removed.'));
        }
        if ($slot->slot_type === 'open_play' && $slot->poster) {
            $posterPath = public_path('assets/img/open-play/' . $slot->poster);
            if (is_file($posterPath)) {
                unlink($posterPath);
            }
        }
        $message = $slot->slot_type === 'open_play'
            ? __('Open Play hours removed successfully.')
            : __('Court block removed successfully.');
        $slot->delete();
        Session::flash('success', $message);

        return redirect()->route('vendor.court_schedule.index');
    }

    private function authorizeOpenPlay(): void
    {
        abort_unless(
            VendorPermissionHelper::hasPackageFeature((int) Auth::guard('vendor')->id(), 'Open Play'),
            403,
            __('Your current package does not include Open Play.')
        );
    }
}
