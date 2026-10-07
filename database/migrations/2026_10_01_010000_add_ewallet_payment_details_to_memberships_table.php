<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->string('payer_account_name')->nullable()->after('payment_method');
            $table->string('payment_transaction_id')->nullable()->after('payer_account_name');
        });
    }

    public function down()
    {
        Schema::table('memberships', function (Blueprint $table) {
            $table->dropColumn(['payer_account_name', 'payment_transaction_id']);
        });
    }
};
