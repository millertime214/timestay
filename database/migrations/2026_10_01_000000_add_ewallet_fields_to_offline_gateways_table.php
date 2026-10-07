<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('offline_gateways', function (Blueprint $table) {
            $table->string('ewallet_type')->nullable()->after('name');
            $table->string('account_name')->nullable()->after('ewallet_type');
            $table->string('account_number')->nullable()->after('account_name');
            $table->string('qr_code')->nullable()->after('account_number');
        });
    }

    public function down()
    {
        Schema::table('offline_gateways', function (Blueprint $table) {
            $table->dropColumn(['ewallet_type', 'account_name', 'account_number', 'qr_code']);
        });
    }
};
