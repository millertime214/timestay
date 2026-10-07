<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('transactions') || !Schema::hasColumn('bookings', 'vendor_ewallet_id')) {
            return;
        }

        DB::table('bookings')
            ->whereNotNull('vendor_ewallet_id')
            ->orderBy('id')
            ->chunkById(100, function ($bookings) {
                foreach ($bookings as $booking) {
                    DB::table('transactions')->updateOrInsert(
                        [
                            'booking_id' => $booking->id,
                            'transcation_type' => 'room_booking',
                            'vendor_id' => $booking->vendor_id,
                        ],
                        [
                            'transcation_id' => $booking->payment_transaction_id ?: $booking->order_number,
                            'user_id' => $booking->user_id,
                            'payment_status' => $booking->payment_status,
                            'payment_method' => $booking->payment_method,
                            'grand_total' => $booking->grand_total,
                            'commission' => 0,
                            'gateway_type' => 'vendor_ewallet',
                            'currency_symbol' => $booking->currency_symbol,
                            'currency_symbol_position' => $booking->currency_symbol_position,
                            'pre_balance' => null,
                            'after_balance' => null,
                            'created_at' => $booking->created_at,
                            'updated_at' => now(),
                        ]
                    );
                }
            });
    }

    public function down(): void
    {
        // Ledger records are retained to preserve the payment audit trail.
    }
};
