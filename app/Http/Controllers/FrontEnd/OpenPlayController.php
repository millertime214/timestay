<?php

namespace App\Http\Controllers\FrontEnd;

use App\Http\Controllers\Controller;
use App\Http\Helpers\VendorPermissionHelper;
use App\Models\CourtBlockedSlot;
use App\Models\RoomContent;
use Illuminate\Http\Request;

class OpenPlayController extends Controller
{
    public function show(int $id)
    {
        $slot = CourtBlockedSlot::with(['court.room_content', 'court.hotel'])
            ->where('slot_type', 'open_play')
            ->where('ends_at', '>', now())
            ->findOrFail($id);

        abort_unless($slot->court, 404);

        $registrationUnavailable = null;
        if ($slot->court->status != 1) {
            $registrationUnavailable = __('This Court is currently inactive.');
        } elseif (!$slot->court->hotel || !$slot->court->hotel->isBookable()) {
            $registrationUnavailable = __('Registration is unavailable while this Site is pending verification or inactive.');
        } elseif (!VendorPermissionHelper::hasPackageFeature((int) $slot->vendor_id, 'Open Play')) {
            $registrationUnavailable = __('Open Play registration is currently unavailable for this vendor.');
        }

        $roomContent = $slot->court->room_content->first();
        $currencyInfo = $this->getCurrencyInfo();
        $remainingPlayers = $slot->remainingPlayerCount();
        $registrationLimit = min($remainingPlayers, 100);

        return view('frontend.room.open-play-register', compact(
            'slot',
            'roomContent',
            'currencyInfo',
            'remainingPlayers',
            'registrationLimit',
            'registrationUnavailable'
        ));
    }

    public function checkout(Request $request, int $id)
    {
        $data = $request->validate([
            'players' => ['required', 'integer', 'min:1', 'max:100'],
            'player_details' => ['required', 'array'],
            'player_details.*.name' => ['required', 'string', 'max:100'],
            'player_details.*.gender' => ['required', 'in:Male,Female,Other,Prefer not to say'],
            'player_details.*.category' => ['required', 'in:Beginner,Intermediate,Advance'],
            'player_details.*.skill_level' => ['required', 'integer', 'between:1,10'],
        ]);

        if (count($data['player_details']) !== (int) $data['players']) {
            return back()->withErrors([
                'players' => __('Please provide information for every player.'),
            ])->withInput();
        }

        $slot = CourtBlockedSlot::with('court.hotel')
            ->where('slot_type', 'open_play')
            ->where('ends_at', '>', now())
            ->findOrFail($id);

        if (!$slot->court || $slot->court->status != 1) {
            return back()->withErrors(['registration' => __('This Court is currently inactive.')])->withInput();
        }
        if (!$slot->court->hotel || !$slot->court->hotel->isBookable()) {
            return back()->withErrors(['registration' => __('Registration is unavailable while this Site is pending verification or inactive.')])->withInput();
        }
        if (!VendorPermissionHelper::hasPackageFeature((int) $slot->vendor_id, 'Open Play')) {
            return back()->withErrors(['registration' => __('Open Play registration is currently unavailable for this vendor.')])->withInput();
        }

        $remainingPlayers = $slot->remainingPlayerCount();
        if ((int) $data['players'] > $remainingPlayers) {
            return back()->withErrors([
                'players' => $remainingPlayers > 0
                    ? __('Only :count player spots are still available.', ['count' => $remainingPlayers])
                    : __('This Open Play session is already full.'),
            ])->withInput();
        }

        $request->session()->put([
            'open_play_slot_id' => $slot->id,
            'room_id' => $slot->room_id,
            'checkInDate' => $slot->starts_at->format('Y-m-d'),
            'checkInTime' => $slot->starts_at->format('H:i:s'),
            'check_out_time' => $slot->ends_at->format('H:i:s'),
            'hour' => $slot->starts_at->floatDiffInHours($slot->ends_at),
            'adult' => $data['players'],
            'children' => 0,
            'open_play_players' => array_values($data['player_details']),
            'classification' => 'open_play',
            // Existing gateway adapters require a price session key. Open Play
            // pricing is resolved from open_play_slot_id instead of this sentinel.
            'price' => -1,
        ]);
        $request->session()->forget(['roomDiscount', 'takeService', 'serviceCharge']);

        return redirect()->route('frontend.room.checkout');
    }
}
