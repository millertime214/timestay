<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingFeatureSlide extends Model
{
    protected $guarded = [];

    protected $casts = ['status' => 'boolean', 'serial_number' => 'integer'];

    public function language()
    {
        return $this->belongsTo(Language::class);
    }
}
