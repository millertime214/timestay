<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_ewallets', function (Blueprint $table) {
            $table->string('verified_name')->nullable()->after('account_number');
            $table->string('tin_number', 30)->nullable()->after('verified_name');
            $table->string('dti_business_name_number', 100)->nullable()->after('tin_number');
            $table->string('profile_screenshot')->nullable()->after('qr_code');
            $table->string('business_registration_document')->nullable()->after('profile_screenshot');
            $table->string('mayors_permit_document')->nullable()->after('business_registration_document');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_ewallets', function (Blueprint $table) {
            $table->dropColumn([
                'verified_name',
                'tin_number',
                'dti_business_name_number',
                'profile_screenshot',
                'business_registration_document',
                'mayors_permit_document',
            ]);
        });
    }
};
