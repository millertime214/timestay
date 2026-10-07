<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorEwallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'name',
        'account_number',
        'verified_name',
        'tin_number',
        'dti_business_name_number',
        'qr_code',
        'profile_screenshot',
        'business_registration_document',
        'mayors_permit_document',
        'status',
    ];

    protected $casts = ['status' => 'boolean'];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
