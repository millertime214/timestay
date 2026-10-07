<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->decimal('monthly_discount', 5, 2)->default(0)->after('price');
            $table->decimal('semi_annual_discount', 5, 2)->default(0)->after('monthly_discount');
            $table->decimal('annual_discount', 5, 2)->default(0)->after('semi_annual_discount');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['monthly_discount', 'semi_annual_discount', 'annual_discount']);
        });
    }
};
