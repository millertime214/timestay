<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    use HasFactory;

    public const SITE_AGREEMENT_VERSION = '2026-10-05';
    protected $fillable = [
        'vendor_id',
        'logo',
        'business_permit_document',
        'business_registration_document',
        'mayors_permit_document',
        'verification_status',
        'verification_notes',
        'verified_by',
        'verified_at',
        'site_agreement_version',
        'site_agreement_accepted_at',
        'average_rating',
        'latitude',
        'longitude',
        'status',
        'max_price',
        'min_price',
        'stars'
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'site_agreement_accepted_at' => 'datetime',
    ];

    public function isBookable(): bool
    {
        return (int) $this->status === 1 && $this->verification_status === 'approved';
    }

    public function isPubliclyVisible(): bool
    {
        return (int) $this->status === 1 || $this->verification_status === 'pending';
    }

    public function scopePubliclyVisible($query)
    {
        return $query->where(function ($query) {
            $query->where('hotels.status', 1)
                ->orWhere('hotels.verification_status', 'pending');
        });
    }
    public function hotel_contents()
    {
        return $this->hasMany(HotelContent::class, 'hotel_id', 'id');
    }
    public function holidays()
    {
        return $this->hasMany(Holiday::class, 'hotel_id', 'id');
    }
    public function room()
    {
        return $this->hasMany(Room::class, 'hotel_id', 'id');
    }

    public function courts()
    {
        return $this->hasMany(Court::class, 'hotel_id', 'id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
    public function hotel_galleries()
    {
        return $this->hasMany(HotelImage::class, 'hotel_id', 'id');
    }
    public function hotel_feature()
    {
        return $this->hasOne(HotelFeature::class, 'hotel_id', 'id');
    }
}
