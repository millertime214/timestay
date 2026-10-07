<?php

namespace App\Models;

/**
 * Pickleball-facing alias for the legacy hotels table.
 *
 * Keeping the existing table lets current vendors, payments and bookings
 * continue to work while new application code can use the sports vocabulary.
 */
class Site extends Hotel
{
    protected $table = 'hotels';

    public function courts()
    {
        return $this->hasMany(Court::class, 'hotel_id', 'id');
    }
}
