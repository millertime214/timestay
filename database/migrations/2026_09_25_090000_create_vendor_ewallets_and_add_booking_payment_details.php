<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('vendor_ewallets')) {
            Schema::create('vendor_ewallets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('vendor_id');
                $table->string('name');
                $table->string('account_number');
                $table->string('qr_code');
                $table->boolean('status')->default(true);
                $table->timestamps();

                $table->index(['vendor_id', 'status'], 'vendor_wallet_status_idx');
            });
        }

        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'vendor_ewallet_id')) {
                $table->unsignedBigInteger('vendor_ewallet_id')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('bookings', 'payment_transaction_id')) {
                $table->string('payment_transaction_id')->nullable()->after('vendor_ewallet_id');
            }
            if (!Schema::hasColumn('bookings', 'payer_phone')) {
                $table->string('payer_phone')->nullable()->after('payment_transaction_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $columns = array_filter(
                ['vendor_ewallet_id', 'payment_transaction_id', 'payer_phone'],
                fn ($column) => Schema::hasColumn('bookings', $column)
            );
            if ($columns) {
                $table->dropColumn($columns);
            }
        });

        Schema::dropIfExists('vendor_ewallets');
    }
};
