<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('court_blocked_slots', function (Blueprint $table) {
            $table->unsignedInteger('max_players')->nullable()->after('price_per_player');
        });

        DB::table('court_blocked_slots')
            ->where('slot_type', 'open_play')
            ->whereNull('max_players')
            ->update(['max_players' => 100]);
    }

    public function down()
    {
        Schema::table('court_blocked_slots', function (Blueprint $table) {
            $table->dropColumn('max_players');
        });
    }
};
