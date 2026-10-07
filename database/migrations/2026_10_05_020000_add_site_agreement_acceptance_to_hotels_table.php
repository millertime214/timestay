<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->string('site_agreement_version', 30)->nullable()->after('verified_at');
            $table->timestamp('site_agreement_accepted_at')->nullable()->after('site_agreement_version');
        });
    }

    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn(['site_agreement_version', 'site_agreement_accepted_at']);
        });
    }
};
