<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->string('business_permit_document')->nullable()->after('logo');
            $table->string('business_registration_document')->nullable()->after('business_permit_document');
            $table->string('mayors_permit_document')->nullable()->after('business_registration_document');
            $table->string('verification_status', 20)->default('pending')->after('mayors_permit_document');
            $table->text('verification_notes')->nullable()->after('verification_status');
            $table->unsignedBigInteger('verified_by')->nullable()->after('verification_notes');
            $table->timestamp('verified_at')->nullable()->after('verified_by');
        });
    }

    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn([
                'business_permit_document', 'business_registration_document',
                'mayors_permit_document', 'verification_status', 'verification_notes',
                'verified_by', 'verified_at',
            ]);
        });
    }
};
