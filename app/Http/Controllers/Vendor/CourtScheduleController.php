<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Helpers\VendorPermissionHelper;
use App\Models\Booking;
use App\Models\BasicSettings\Basic;
use App\Models\CourtBlockedSlot;
use App\Models\Language;
use App\Models\OpenPlayMatch;
use App\Models\Room;
use App\Support\OpenPlayMatchmaker;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

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
        $canUseMatchmaker = VendorPermissionHelper::hasPackageFeature(
            (int) Auth::guard('vendor')->id(),
            'Pickleball Match Maker'
        );
        if ($canUseMatchmaker) {
            app(OpenPlayMatchmaker::class)->syncRoster($slot);
        }
        $slot->load(['rosterEntries' => fn ($query) => $query->where('active', true)->orderByDesc('wait_rounds')->orderBy('queue_position')]);
        $latestBracket = (int) $slot->matches()->max('bracket_number');
        $matchQuery = $slot->matches()->with('players');
        if ($slot->play_format === 'single_elimination') {
            $matchQuery->where('bracket_number', $latestBracket);
        }
        $matches = $matchQuery->orderByDesc('round_number')->orderBy('court_number')->get();
        $previousTournamentMatches = $slot->play_format === 'single_elimination' && $latestBracket > 1
            ? $slot->matches()->with('players')->where('bracket_number', '<', $latestBracket)->orderByDesc('bracket_number')->orderByDesc('round_number')->get()
            : collect();

        return view('vendors.room-booking.open-play-details', compact('slot', 'language', 'canUseMatchmaker', 'matches', 'previousTournamentMatches', 'latestBracket'));
    }

    public function generateOpenPlayRound(Request $request, int $id)
    {
        $this->authorizeOpenPlay();
        $this->authorizeMatchmaker();
        $data = $request->validate([
            'court_count' => ['required', 'integer', 'min:1', 'max:20'],
            'skill_separated' => ['nullable', 'boolean'],
        ]);
        $slot = CourtBlockedSlot::where('vendor_id', Auth::guard('vendor')->id())
            ->where('slot_type', 'open_play')->findOrFail($id);

        $matchmaker = app(OpenPlayMatchmaker::class);
        if ($slot->play_format === 'single_elimination') {
            $matchmaker->generateTournament($slot);
        } else {
            $matchmaker->generate($slot, (int) $data['court_count'], (bool) ($data['skill_separated'] ?? false));
        }

        $message = $slot->play_format === 'single_elimination'
            ? __('A new tournament bracket was generated. Previous tournament results were retained.')
            : __('The next balanced Open Play round was generated.');
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['message' => $message]);
        }
        Session::flash('success', $message);
        return redirect()->route('vendor.court_schedule.open_play.show', ['id' => $slot->id, 'language' => $request->input('language')])
            ->withFragment('generated-rounds');
    }

    public function addOpenPlayPlayer(Request $request, int $id)
    {
        $this->authorizeOpenPlay();
        $this->authorizeMatchmaker();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'max:40'],
            'category' => ['required', 'string', 'max:40'],
            'skill_rating' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $slot = CourtBlockedSlot::where('vendor_id', Auth::guard('vendor')->id())
            ->where('slot_type', 'open_play')->findOrFail($id);

        DB::transaction(function () use ($slot, $data) {
            $lockedSlot = CourtBlockedSlot::query()->lockForUpdate()->findOrFail($slot->id);
            if ($lockedSlot->registeredPlayerCount() >= (int) $lockedSlot->max_players) {
                throw ValidationException::withMessages([
                    'name' => __('This Open Play session has reached its player capacity.'),
                ]);
            }
            $lockedSlot->rosterEntries()->create([
                'booking_id' => null,
                'player_index' => 0,
                'name' => $data['name'],
                'gender' => $data['gender'] ?? null,
                'category' => $data['category'],
                'skill_rating' => $data['skill_rating'],
                'queue_position' => ((int) $lockedSlot->rosterEntries()->max('queue_position')) + 1,
                'active' => true,
                'joined_at' => now(),
            ]);
        });

        Session::flash('success', __('Player added to the Open Play rotation queue.'));
        return redirect()->route('vendor.court_schedule.open_play.show', [
            'id' => $slot->id,
            'language' => $request->input('language'),
        ]);
    }

    public function completeOpenPlayMatch(Request $request, int $id)
    {
        $this->authorizeOpenPlay();
        $this->authorizeMatchmaker();
        $data = $request->validate([
            'team_one_score' => ['required', 'integer', 'min:0', 'max:999'],
            'team_two_score' => ['required', 'integer', 'min:0', 'max:999', 'different:team_one_score'],
        ], [
            'team_two_score.different' => __('Open Play matches cannot be saved with a tied score.'),
        ]);
        $match = DB::transaction(function () use ($id, $data) {
            $match = OpenPlayMatch::where('vendor_id', Auth::guard('vendor')->id())->lockForUpdate()->findOrFail($id);
            abort_if($match->status === 'completed', 409, __('This match result has already been recorded.'));
            abort_unless($match->players()->wherePivot('team', 1)->exists() && $match->players()->wherePivot('team', 2)->exists(), 409, __('Both teams must be known before recording a result.'));
            $match->update([
                'status' => 'completed',
                'team_one_score' => $data['team_one_score'],
                'team_two_score' => $data['team_two_score'],
                'winner_team' => $data['team_one_score'] > $data['team_two_score'] ? 1 : 2,
                'completed_at' => now(),
            ]);
            $match->load(['players', 'nextMatch']);
            if ($match->slot->play_format === 'single_elimination') {
                $matchmaker = app(OpenPlayMatchmaker::class);
                $matchmaker->advanceTournamentWinner($match);
                $matchmaker->advanceAutomaticByes($match->slot);
            }

            return $match;
        });

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json(['message' => __('Match result saved successfully.')]);
        }
        Session::flash('success', __('Match result saved successfully.'));
        return redirect()->route('vendor.court_schedule.open_play.show', [
            'id' => $match->court_blocked_slot_id,
            'language' => $request->input('language'),
        ])->withFragment('generated-rounds');
    }

    public function tournamentDisplay(int $id)
    {
        $slot = $this->tournamentSlot($id);

        return view('vendors.court-display.tournament', compact('slot'));
    }

    public function tournamentData(int $id)
    {
        $slot = $this->tournamentSlot($id);
        $bracketNumber = (int) $slot->matches()->max('bracket_number');
        $matches = $slot->matches()->where('bracket_number', $bracketNumber)->with('players')->orderBy('round_number')->orderBy('bracket_position')->get();
        $roundCount = (int) $matches->max('round_number');

        return response()->json([
            'title' => $slot->title ?: __('Open Play Tournament'),
            'bracket_number' => $bracketNumber,
            'status' => optional($matches->first(fn ($match) => $match->round_number === $roundCount && $match->status === 'completed' && $match->winner_team))->status ? 'completed' : 'live',
            'rounds' => $matches->groupBy('round_number')->map(function ($roundMatches, $round) use ($roundCount) {
                return [
                    'number' => (int) $round,
                    'label' => $round == $roundCount ? __('Final') : ($round == $roundCount - 1 ? __('Semifinal') : __('Round') . ' ' . $round),
                    'matches' => $roundMatches->map(function ($match) {
                        $team = fn ($number) => $match->players->where('pivot.team', $number)->pluck('name')->values()->all();
                        return [
                            'id' => $match->id,
                            'position' => $match->bracket_position,
                            'status' => $match->status,
                            'team_one' => $team(1),
                            'team_two' => $team(2),
                            'team_one_score' => $match->team_one_score,
                            'team_two_score' => $match->team_two_score,
                            'winner_team' => $match->winner_team,
                        ];
                    })->values(),
                ];
            })->values(),
        ]);
    }

    public function rotationDisplay(int $id)
    {
        $slot = $this->rotationSlot($id);

        return view('vendors.court-display.rotation', compact('slot'));
    }

    public function rotationData(int $id)
    {
        $slot = $this->rotationSlot($id);
        app(OpenPlayMatchmaker::class)->syncRoster($slot);
        $timezone = optional(Basic::first())->timezone ?: config('app.timezone', 'UTC');
        $now = Carbon::now($timezone);
        $players = $slot->rosterEntries()->where('active', true)
            ->withCount(['matches as completed_games_count' => fn ($query) => $query->where('open_play_matches.status', 'completed')])
            ->orderByDesc('wait_rounds')->orderBy('matches_played')->orderBy('queue_position')->get();
        $scheduledMatches = $slot->matches()->where('status', 'scheduled')->with('players')
            ->orderBy('round_number')->orderBy('court_number')->get();
        $currentRound = (int) ($scheduledMatches->max('round_number') ?: $slot->matches()->max('round_number'));
        $currentMatches = $scheduledMatches->where('round_number', $currentRound)->values();
        $onCourtIds = $currentMatches->flatMap(fn ($match) => $match->players->pluck('id'))->unique();
        $waiting = $players->reject(fn ($player) => $onCourtIds->contains($player->id))->values();
        $nextCapacity = max($currentMatches->count(), 1) * 4;
        $playerData = fn ($player) => [
            'id' => $player->id,
            'name' => $player->name,
            'category' => $player->category ?: __('Unrated'),
            'rating' => $player->skill_rating,
            'games_completed' => (int) ($player->completed_games_count ?? 0),
            'games_assigned' => (int) $player->matches_played,
            'wait_rounds' => (int) $player->wait_rounds,
        ];
        $recentResults = $slot->matches()->where('status', 'completed')->whereNotNull('winner_team')
            ->with('players')->orderByDesc('completed_at')->take(4)->get();

        return response()->json([
            'title' => $slot->title ?: __('Open Play Rotation'),
            'court' => optional(optional($slot->court)->room_content->first())->title ?: __('Court') . ' #' . $slot->room_id,
            'server_time' => $now->format('Y-m-d\TH:i:s'),
            'starts_at' => Carbon::parse($slot->getRawOriginal('starts_at'), $timezone)->format('M d, h:i A'),
            'ends_at' => Carbon::parse($slot->getRawOriginal('ends_at'), $timezone)->format('M d, h:i A'),
            'session_status' => $now->lt(Carbon::parse($slot->getRawOriginal('starts_at'), $timezone)) ? 'upcoming' : ($now->gte(Carbon::parse($slot->getRawOriginal('ends_at'), $timezone)) ? 'ended' : 'live'),
            'current_round' => $currentRound,
            'active_players' => $players->count(),
            'players_on_court' => $onCourtIds->count(),
            'waiting_count' => $waiting->count(),
            'completed_matches' => $slot->matches()->where('status', 'completed')->whereNotNull('winner_team')->count(),
            'average_games' => $players->count() ? round($players->avg('completed_games_count'), 1) : 0,
            'current_matches' => $currentMatches->map(function ($match) use ($players, $playerData) {
                $serializeTeam = fn ($team) => $match->players->where('pivot.team', $team)->map(function ($player) use ($players, $playerData) {
                    return $playerData($players->firstWhere('id', $player->id) ?: $player);
                })->values();
                return [
                    'id' => $match->id,
                    'complete_url' => route('vendor.court_schedule.open_play.matches.complete', $match->id),
                    'court_number' => $match->court_number,
                    'skill_group' => $match->skill_group,
                    'team_one' => $serializeTeam(1),
                    'team_two' => $serializeTeam(2),
                ];
            }),
            'next_up' => $waiting->take($nextCapacity)->map($playerData)->values(),
            'waiting' => $waiting->slice($nextCapacity)->map($playerData)->values(),
            'recent_results' => $recentResults->map(function ($match) {
                $names = fn ($team) => $match->players->where('pivot.team', $team)->pluck('name')->values();
                return [
                    'round' => $match->round_number,
                    'court_number' => $match->court_number,
                    'team_one' => $names(1),
                    'team_two' => $names(2),
                    'team_one_score' => $match->team_one_score,
                    'team_two_score' => $match->team_two_score,
                    'winner_team' => $match->winner_team,
                ];
            }),
        ]);
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
            'play_format' => 'rotation',
        ], $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'room_id' => ['required', 'integer'],
            'starts_at' => ['required', 'date', 'after_or_equal:now'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'price_per_player' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
            'max_players' => ['required', 'integer', 'min:1', 'max:1000'],
            'play_format' => ['required', 'in:rotation,single_elimination'],
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
            'play_format' => $data['play_format'],
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

    private function authorizeMatchmaker(): void
    {
        abort_unless(
            VendorPermissionHelper::hasPackageFeature((int) Auth::guard('vendor')->id(), 'Pickleball Match Maker'),
            403,
            __('Your current package does not include Pickleball Match Maker.')
        );
    }

    private function tournamentSlot(int $id): CourtBlockedSlot
    {
        $this->authorizeOpenPlay();
        $this->authorizeMatchmaker();

        return CourtBlockedSlot::where('vendor_id', Auth::guard('vendor')->id())
            ->where('slot_type', 'open_play')
            ->where('play_format', 'single_elimination')
            ->findOrFail($id);
    }

    private function rotationSlot(int $id): CourtBlockedSlot
    {
        $this->authorizeOpenPlay();
        $this->authorizeMatchmaker();

        return CourtBlockedSlot::with('court.room_content')
            ->where('vendor_id', Auth::guard('vendor')->id())
            ->where('slot_type', 'open_play')
            ->where('play_format', 'rotation')
            ->findOrFail($id);
    }
}
