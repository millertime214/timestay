<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_price',
        'discount',
        'coupon_code',
        'price',
        'currency',
        'currency_symbol',
        'payment_method',
        'payer_account_name',
        'payment_transaction_id',
        'transaction_id',
        'status',
        'is_trial',
        'trial_days',
        'receipt',
        'transaction_details',
        'settings',
        'package_id',
        'billing_term',
        'vendor_id',
        'start_date',
        'expire_date',
        'conversation_id',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }
}
