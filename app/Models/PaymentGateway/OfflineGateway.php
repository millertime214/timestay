<?php

namespace App\Models\PaymentGateway;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfflineGateway extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'name',
    'ewallet_type',
    'account_name',
    'account_number',
    'qr_code',
    'short_description',
    'instructions',
    'status',
    'has_attachment',
    'serial_number'
  ];
}
