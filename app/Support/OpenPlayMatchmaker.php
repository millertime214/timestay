<?php

namespace App\Support;

use App\Models\CourtBlockedSlot;
use App\Models\OpenPlayMatch;
use App\Models\OpenPlayRosterEntry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OpenPlayMatchmaker
{
    public function syncRoster(CourtBlockedSlot $slot): Collection
    {
        $registrations = $slot->registrations()
            ->where('payment_status', '!=', 2)
            ->orderBy('created_at')
            ->get();
        $activeKeys = [];
        $nextPosition = (int) $slot->rosterEntries()->max('queue_position');

        foreach ($registrations as $registration) {
            $players = $registration->open_play_players ?: [];
            if (!$players) {
                $players = collect(range(1, max((int) $registration->adult, 1)))->map(fn ($number) => [
                    'name' => ($registration->booking_name ?: __('Guest')) . ' ' . __('Player') . ' ' . $number,
                    'gender' => null,
                    'category' => $slot->category ?: 'Intermediate',
                    'skill_level' => 5,
                ])->all();
            }

            foreach (array_values($players) as $index => $player) {
                $key = $registration->id . ':' . $index;
                $activeKeys[] = $key;
                $existing = $slot->rosterEntries()
                    ->where('booking_id', $registration->id)
                    ->where('player_index', $index)
                    ->first();

                $slot->rosterEntries()->updateOrCreate(
                    ['booking_id' => $registration->id, 'player_index' => $index],
                    [
                        'name' => $player['name'] ?? __('Player'),
                        'gender' => $player['gender'] ?? null,
                        'category' => $player['category'] ?? ($slot->category ?: 'Intermediate'),
                        'skill_rating' => max(1, min(10, (int) ($player['skill_level'] ?? 5))),
                        'queue_position' => $existing?->queue_position ?: ++$nextPosition,
                        'active' => true,
                        'joined_at' => $existing?->joined_at ?: ($registration->created_at ?: now()),
                    ]
                );
            }
        }

        $slot->rosterEntries()->whereNotNull('booking_id')->get()->each(function ($entry) use ($activeKeys) {
            $entry->update([
                'active' => in_array($entry->booking_id . ':' . $entry->player_index, $activeKeys, true),
            ]);
        });

        return $slot->rosterEntries()->where('active', true)->orderBy('queue_position')->get();
    }

    public function generate(CourtBlockedSlot $slot, int $courtCount = 1, bool $skillSeparated = true): Collection
    {
        return DB::transaction(function () use ($slot, $courtCount, $skillSeparated) {
            $slot = CourtBlockedSlot::query()->lockForUpdate()->findOrFail($slot->id);
            $players = $this->syncRoster($slot);

            if ($slot->matches()->where('status', 'scheduled')->exists()) {
                throw ValidationException::withMessages([
                    'round' => __('Complete the current round before generating another one.'),
                ]);
            }

            if ($players->count() < 4) {
                throw ValidationException::withMessages([
                    'round' => __('At least four active players are required to generate a match.'),
                ]);
            }

            $courtCount = max(1, min($courtCount, intdiv($players->count(), 4)));
            $round = ((int) $slot->matches()->max('round_number')) + 1;
            [$partnerHistory, $opponentHistory] = $this->history($slot);
            $pool = $players->sortBy([
                ['wait_rounds', 'desc'],
                ['matches_played', 'asc'],
                ['queue_position', 'asc'],
            ])->values();
            $matches = collect();
            $selectedIds = collect();

            for ($court = 1; $court <= $courtCount; $court++) {
                $group = $this->nextGroup($pool, $skillSeparated);
                if ($group->count() < 4) {
                    break;
                }

                [$teamOne, $teamTwo] = self::balanceTeams($group, $partnerHistory, $opponentHistory);
                $match = OpenPlayMatch::create([
                    'court_blocked_slot_id' => $slot->id,
                    'vendor_id' => $slot->vendor_id,
                    'round_number' => $round,
                    'court_number' => $court,
                    'skill_group' => $skillSeparated ? $group->pluck('category')->filter()->unique()->implode(' / ') : null,
                    'status' => 'scheduled',
                ]);

                $match->players()->attach($teamOne->pluck('id')->mapWithKeys(fn ($id) => [$id => ['team' => 1]])->all());
                $match->players()->attach($teamTwo->pluck('id')->mapWithKeys(fn ($id) => [$id => ['team' => 2]])->all());
                $match->load('players');
                $matches->push($match);
                $ids = $group->pluck('id');
                $selectedIds = $selectedIds->merge($ids);
                $pool = $pool->reject(fn ($player) => $ids->contains($player->id))->values();
            }

            if ($matches->isEmpty()) {
                throw ValidationException::withMessages(['round' => __('Unable to form a complete four-player match.')]);
            }

            OpenPlayRosterEntry::whereIn('id', $selectedIds)->update([
                'wait_rounds' => 0,
                'matches_played' => DB::raw('matches_played + 1'),
            ]);
            $waitingIds = $players->pluck('id')->diff($selectedIds);
            OpenPlayRosterEntry::whereIn('id', $waitingIds)->update([
                'wait_rounds' => DB::raw('wait_rounds + 1'),
            ]);

            return $matches;
        });
    }

    public function generateTournament(CourtBlockedSlot $slot): Collection
    {
        return DB::transaction(function () use ($slot) {
            $slot = CourtBlockedSlot::query()->lockForUpdate()->findOrFail($slot->id);
            $players = $this->syncRoster($slot)->sortByDesc('skill_rating')->values();

            $latestBracket = (int) $slot->matches()->max('bracket_number');
            $currentMatches = $slot->matches()->where('bracket_number', $latestBracket)->get();
            if ($currentMatches->contains('status', 'scheduled')) {
                $hasRecordedResults = $currentMatches->contains(fn ($match) => !is_null($match->team_one_score) || !is_null($match->team_two_score));
                if ($hasRecordedResults) {
                    throw ValidationException::withMessages([
                        'round' => __('Complete the current tournament before regenerating its bracket.'),
                    ]);
                }

                // An untouched bracket can be rebuilt safely, for example after
                // late player arrivals or a corrected seed layout.
                $slot->matches()->where('bracket_number', $latestBracket)->delete();
            }
            $bracketNumber = $currentMatches->contains('status', 'scheduled') ? max($latestBracket, 1) : $latestBracket + 1;
            if ($players->count() < 4 || $players->count() % 2 !== 0) {
                throw ValidationException::withMessages([
                    'round' => __('A single-elimination doubles tournament requires an even number of at least four active players.'),
                ]);
            }

            $teams = collect();
            while ($players->isNotEmpty()) {
                $teams->push(collect([$players->shift(), $players->pop()]));
            }
            $teams = $teams->sortByDesc(fn ($team) => $team->sum('skill_rating'))->values();
            $bracketSize = 1;
            while ($bracketSize < $teams->count()) {
                $bracketSize *= 2;
            }
            $roundCount = (int) log($bracketSize, 2);
            $nodes = [];

            for ($round = $roundCount; $round >= 1; $round--) {
                $matchCount = intdiv($bracketSize, 2 ** $round);
                for ($position = 1; $position <= $matchCount; $position++) {
                    $next = $round < $roundCount ? $nodes[$round + 1][(int) ceil($position / 2)] : null;
                    $nodes[$round][$position] = OpenPlayMatch::create([
                        'court_blocked_slot_id' => $slot->id,
                        'bracket_number' => $bracketNumber,
                        'vendor_id' => $slot->vendor_id,
                        'round_number' => $round,
                        'court_number' => $position,
                        'bracket_position' => $position,
                        'next_match_id' => $next?->id,
                        'status' => 'scheduled',
                    ]);
                }
            }

            for ($position = 1; $position <= intdiv($bracketSize, 2); $position++) {
                $match = $nodes[1][$position];
                $matchCount = intdiv($bracketSize, 2);
                $teamOne = $teams->get($position - 1);
                $teamTwo = $teams->get($matchCount + $position - 1);
                if ($teamOne) {
                    $match->players()->attach($teamOne->pluck('id')->mapWithKeys(fn ($id) => [$id => ['team' => 1]])->all());
                }
                if ($teamTwo) {
                    $match->players()->attach($teamTwo->pluck('id')->mapWithKeys(fn ($id) => [$id => ['team' => 2]])->all());
                }
            }

            $this->advanceAutomaticByes($slot, $bracketNumber);
            return $slot->matches()->where('bracket_number', $bracketNumber)->with('players')->orderBy('round_number')->orderBy('bracket_position')->get();
        });
    }

    public function advanceTournamentWinner(OpenPlayMatch $match): void
    {
        if (!$match->next_match_id || !$match->winner_team) {
            return;
        }

        $side = $match->bracket_position % 2 === 1 ? 1 : 2;
        $winnerIds = $match->players()->wherePivot('team', $match->winner_team)->pluck('open_play_roster_entries.id');
        $match->nextMatch->players()->attach($winnerIds->mapWithKeys(fn ($id) => [$id => ['team' => $side]])->all());
    }

    public function advanceAutomaticByes(CourtBlockedSlot $slot, ?int $bracketNumber = null): void
    {
        $bracketNumber = $bracketNumber ?: (int) $slot->matches()->max('bracket_number');
        $matches = $slot->matches()->where('bracket_number', $bracketNumber)->with(['players', 'nextMatch'])->orderBy('round_number')->orderBy('bracket_position')->get();
        foreach ($matches as $match) {
            $match->load('players');
            if ($match->status === 'completed') {
                continue;
            }
            $feeders = $slot->matches()->where('bracket_number', $bracketNumber)->where('next_match_id', $match->id)->get();
            $ready = $match->round_number === 1 || ($feeders->count() === 2 && $feeders->every(fn ($feeder) => $feeder->status === 'completed'));
            if (!$ready || $match->players->count() > 2) {
                continue;
            }

            $team = $match->players->groupBy('pivot.team')->keys()->first();
            $match->update([
                'status' => 'completed',
                'winner_team' => $team ? (int) $team : null,
                'completed_at' => now(),
            ]);
            $match->load(['players', 'nextMatch']);
            $this->advanceTournamentWinner($match);
            $match->nextMatch?->load('players');
        }
    }

    public static function balanceTeams(Collection $players, array $partnerHistory = [], array $opponentHistory = []): array
    {
        $players = $players->values();
        $splits = [
            [[0, 1], [2, 3]],
            [[0, 2], [1, 3]],
            [[0, 3], [1, 2]],
        ];
        $best = null;

        foreach ($splits as [$left, $right]) {
            $teamOne = collect([$players[$left[0]], $players[$left[1]]]);
            $teamTwo = collect([$players[$right[0]], $players[$right[1]]]);
            $partnerRepeats = self::pairCount($partnerHistory, $teamOne[0]->id, $teamOne[1]->id)
                + self::pairCount($partnerHistory, $teamTwo[0]->id, $teamTwo[1]->id);
            $opponentRepeats = 0;
            foreach ($teamOne as $one) {
                foreach ($teamTwo as $two) {
                    $opponentRepeats += self::pairCount($opponentHistory, $one->id, $two->id);
                }
            }
            $skillDifference = abs($teamOne->sum('skill_rating') - $teamTwo->sum('skill_rating'));
            $score = ($partnerRepeats * 1000) + ($skillDifference * 20) + $opponentRepeats;

            if ($best === null || $score < $best['score']) {
                $best = compact('score', 'teamOne', 'teamTwo');
            }
        }

        return [$best['teamOne'], $best['teamTwo']];
    }

    private function nextGroup(Collection $pool, bool $skillSeparated): Collection
    {
        if (!$skillSeparated) {
            return $pool->take(4)->values();
        }

        $eligibleGroups = $pool->groupBy(fn ($player) => $player->category ?: 'Unrated')
            ->filter(fn ($group) => $group->count() >= 4);

        if ($eligibleGroups->isEmpty()) {
            return $pool->take(4)->values();
        }

        return $eligibleGroups->sortByDesc(function ($group) {
            $first = $group->first();
            return ($first->wait_rounds * 10000) - ($first->matches_played * 100) - $first->queue_position;
        })->first()->take(4)->values();
    }

    private function history(CourtBlockedSlot $slot): array
    {
        $partnerHistory = [];
        $opponentHistory = [];
        $slot->matches()->with('players')->get()->each(function ($match) use (&$partnerHistory, &$opponentHistory) {
            $teamOne = $match->players->where('pivot.team', 1)->values();
            $teamTwo = $match->players->where('pivot.team', 2)->values();
            if ($teamOne->count() === 2) {
                self::incrementPair($partnerHistory, $teamOne[0]->id, $teamOne[1]->id);
            }
            if ($teamTwo->count() === 2) {
                self::incrementPair($partnerHistory, $teamTwo[0]->id, $teamTwo[1]->id);
            }
            foreach ($teamOne as $one) {
                foreach ($teamTwo as $two) {
                    self::incrementPair($opponentHistory, $one->id, $two->id);
                }
            }
        });

        return [$partnerHistory, $opponentHistory];
    }

    private static function pairCount(array $history, int $first, int $second): int
    {
        return $history[self::pairKey($first, $second)] ?? 0;
    }

    private static function incrementPair(array &$history, int $first, int $second): void
    {
        $key = self::pairKey($first, $second);
        $history[$key] = ($history[$key] ?? 0) + 1;
    }

    private static function pairKey(int $first, int $second): string
    {
        return min($first, $second) . ':' . max($first, $second);
    }
}
