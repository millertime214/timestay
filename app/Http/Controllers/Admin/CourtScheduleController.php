<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourtBlockedSlot;
use App\Models\Language;
use Illuminate\Http\Request;

class CourtScheduleController extends Controller
{
    public function index(Request $request)
    {
        $language = Language::where('code', $request->input('language'))->first()
            ?: Language::where('is_default', 1)->first();

        $slots = CourtBlockedSlot::with(['court.room_content', 'vendor'])
            ->withCount('registrations')
            ->where('slot_type', 'open_play')
            ->orderByDesc('starts_at')
            ->paginate(20);

        return view('admin.room-booking.open-play-index', compact('slots', 'language'));
    }

    public function showOpenPlay(int $id)
    {
        $slot = CourtBlockedSlot::with(['court.room_content', 'registrations.user'])
            ->where('slot_type', 'open_play')
            ->findOrFail($id);

        $language = Language::where('code', request()->input('language'))->first()
            ?: Language::where('is_default', 1)->first();

        return view('admin.room-booking.open-play-details', compact('slot', 'language'));
    }
}
