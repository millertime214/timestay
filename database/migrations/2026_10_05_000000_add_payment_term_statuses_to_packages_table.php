<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->boolean('monthly_term_active')->default(true)->after('annual_discount');
            $table->boolean('semi_annual_term_active')->default(true)->after('monthly_term_active');
            $table->boolean('annual_term_active')->default(true)->after('semi_annual_term_active');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn([
                'monthly_term_active',
                'semi_annual_term_active',
                'annual_term_active',
            ]);
        });
    }
};
