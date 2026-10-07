<?php

namespace App\Models;

/** Pickleball-facing alias for the legacy rooms table. */
class Court extends Room
{
    protected $table = 'rooms';

    public function site()
    {
        return $this->belongsTo(Site::class, 'hotel_id', 'id');
    }
}
