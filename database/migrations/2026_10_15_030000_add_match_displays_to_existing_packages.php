<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('packages')->select('id', 'features')->orderBy('id')->get()->each(function ($package) {
            $features = json_decode($package->features ?: '[]', true);
            if (!is_array($features) || !in_array('Pickleball Match Maker', $features, true)) return;
            foreach (['Rotation Player Display', 'Tournament Display'] as $feature) {
                if (!in_array($feature, $features, true)) $features[] = $feature;
            }
            DB::table('packages')->where('id', $package->id)->update(['features' => json_encode(array_values($features))]);
        });
    }

    public function down(): void
    {
        DB::table('packages')->select('id', 'features')->orderBy('id')->get()->each(function ($package) {
            $features = json_decode($package->features ?: '[]', true);
            if (!is_array($features)) return;
            $features = array_values(array_diff($features, ['Rotation Player Display', 'Tournament Display']));
            DB::table('packages')->where('id', $package->id)->update(['features' => json_encode($features)]);
        });
    }
};
