<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpenPlayMatch extends Model
{
    protected $fillable = [
        'court_blocked_slot_id', 'vendor_id', 'round_number', 'court_number',
        'skill_group', 'status', 'team_one_score', 'team_two_score', 'winner_team', 'completed_at',
    ];

    protected $casts = [
        'team_one_score' => 'integer',
        'team_two_score' => 'integer',
        'winner_team' => 'integer',
        'completed_at' => 'datetime',
    ];

    public function players()
    {
        return $this->belongsToMany(OpenPlayRosterEntry::class, 'open_play_match_players')
            ->withPivot('team')->withTimestamps();
    }

    public function slot()
    {
        return $this->belongsTo(CourtBlockedSlot::class, 'court_blocked_slot_id');
    }
}
