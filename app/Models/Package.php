<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'price',
        'monthly_discount',
        'semi_annual_discount',
        'annual_discount',
        'monthly_term_active',
        'semi_annual_term_active',
        'annual_term_active',
        'term',
        'status',
        'number_of_car_featured',
        'number_of_listing',
        'number_of_hotel',
        'number_of_amenities_per_room',
        'number_of_room',
        'number_of_amenities_per_hotel',
        'number_of_bookings',
        'number_of_images_per_hotel',
        'number_of_images_per_room',
        'number_of_products',
        'slug',
        'custom_features',
        'features',
        'number_of_faq',
        'number_of_social_links',
        'number_of_additional_specification',
        'icon',
        'recommended'
    ];

    protected $casts = [
        'monthly_discount' => 'decimal:2',
        'semi_annual_discount' => 'decimal:2',
        'annual_discount' => 'decimal:2',
        'monthly_term_active' => 'boolean',
        'semi_annual_term_active' => 'boolean',
        'annual_term_active' => 'boolean',
    ];

    public function enabledBillingTerms(): array
    {
        return array_filter([
            'monthly' => $this->monthly_term_active,
            'semi-annual' => $this->semi_annual_term_active,
            'yearly' => $this->annual_term_active,
        ]);
    }

    public function isBillingTermEnabled(string $term): bool
    {
        return array_key_exists($term, $this->enabledBillingTerms());
    }

    public function discountForBillingTerm(string $term): float
    {
        $column = [
            'monthly' => 'monthly_discount',
            'semi-annual' => 'semi_annual_discount',
            'yearly' => 'annual_discount',
        ][$term] ?? null;

        return $column ? (float) $this->{$column} : 0.0;
    }

    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }
}
