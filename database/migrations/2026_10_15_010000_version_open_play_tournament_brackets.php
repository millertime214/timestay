<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('open_play_matches', 'bracket_number')) {
            Schema::table('open_play_matches', function (Blueprint $table) {
                $table->unsignedInteger('bracket_number')->default(1)->after('court_blocked_slot_id');
            });
        }

        Schema::table('open_play_matches', function (Blueprint $table) {
            // Keep a dedicated index for the slot foreign key before replacing
            // the original unique index that MySQL may also use for that FK.
            $table->index('court_blocked_slot_id', 'open_play_matches_slot_fk_index');
            $table->dropUnique('open_play_round_court_unique');
            $table->unique(
                ['court_blocked_slot_id', 'bracket_number', 'round_number', 'court_number'],
                'open_play_bracket_round_court_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('open_play_matches', function (Blueprint $table) {
            $table->dropUnique('open_play_bracket_round_court_unique');
            $table->unique(
                ['court_blocked_slot_id', 'round_number', 'court_number'],
                'open_play_round_court_unique'
            );
            $table->dropIndex('open_play_matches_slot_fk_index');
            $table->dropColumn('bracket_number');
        });
    }
};
