<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('court_blocked_slots', function (Blueprint $table) {
            $table->string('slot_type', 20)->default('blocked')->after('reason');
            $table->decimal('price_per_player', 10, 2)->nullable()->after('slot_type');
            $table->index(['room_id', 'slot_type', 'starts_at'], 'court_slot_type_start_idx');
        });
    }

    public function down(): void
    {
        Schema::table('court_blocked_slots', function (Blueprint $table) {
            $table->dropIndex('court_slot_type_start_idx');
            $table->dropColumn(['slot_type', 'price_per_player']);
        });
    }
};
