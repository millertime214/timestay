<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpenPlayRosterEntry extends Model
{
    protected $fillable = [
        'court_blocked_slot_id', 'booking_id', 'player_index', 'name', 'gender', 'category',
        'skill_rating', 'queue_position', 'matches_played', 'wait_rounds', 'active', 'joined_at',
    ];

    protected $casts = [
        'skill_rating' => 'integer',
        'queue_position' => 'integer',
        'matches_played' => 'integer',
        'wait_rounds' => 'integer',
        'active' => 'boolean',
        'joined_at' => 'datetime',
    ];

    public function slot()
    {
        return $this->belongsTo(CourtBlockedSlot::class, 'court_blocked_slot_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
