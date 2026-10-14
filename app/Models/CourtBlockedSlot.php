<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourtBlockedSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id', 'room_id', 'starts_at', 'ends_at', 'reason', 'slot_type', 'title', 'category', 'price_per_player',
        'max_players', 'poster',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'price_per_player' => 'decimal:2',
        'max_players' => 'integer',
    ];

    public function court()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function registrations()
    {
        return $this->hasMany(Booking::class, 'open_play_slot_id');
    }

    public function rosterEntries()
    {
        return $this->hasMany(OpenPlayRosterEntry::class);
    }

    public function matches()
    {
        return $this->hasMany(OpenPlayMatch::class);
    }

    public function registeredPlayerCount(): int
    {
        $bookedPlayers = (int) $this->registrations()
            ->where('payment_status', '!=', 2)
            ->sum('adult');
        $manualPlayers = (int) $this->rosterEntries()
            ->whereNull('booking_id')
            ->where('active', true)
            ->count();

        return $bookedPlayers + $manualPlayers;
    }

    public function remainingPlayerCount(): int
    {
        return max((int) $this->max_players - $this->registeredPlayerCount(), 0);
    }

    public function scopeOverlapping($query, int $roomId, $startsAt, $endsAt)
    {
        return $query->where('room_id', $roomId)
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt);
    }
}
