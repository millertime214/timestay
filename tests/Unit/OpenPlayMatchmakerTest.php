<?php

namespace Tests\Unit;

use App\Models\OpenPlayRosterEntry;
use App\Support\OpenPlayMatchmaker;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class OpenPlayMatchmakerTest extends TestCase
{
    public function test_it_balances_team_skill_totals(): void
    {
        [$teamOne, $teamTwo] = OpenPlayMatchmaker::balanceTeams($this->players([10, 8, 4, 2]));

        $this->assertSame($teamOne->sum('skill_rating'), $teamTwo->sum('skill_rating'));
    }

    public function test_it_avoids_repeating_past_partners_when_an_alternative_exists(): void
    {
        $players = $this->players([8, 8, 7, 7]);
        [$teamOne, $teamTwo] = OpenPlayMatchmaker::balanceTeams($players, ['1:2' => 3]);
        $teams = [$teamOne->pluck('id')->sort()->values()->all(), $teamTwo->pluck('id')->sort()->values()->all()];

        $this->assertNotContains([1, 2], $teams);
    }

    private function players(array $ratings): Collection
    {
        return collect($ratings)->map(function ($rating, $index) {
            $player = new OpenPlayRosterEntry(['skill_rating' => $rating]);
            $player->id = $index + 1;

            return $player;
        });
    }
}
