<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('court_blocked_slots', function (Blueprint $table) {
            $table->string('play_format', 30)->default('rotation')->after('slot_type');
        });

        Schema::table('open_play_matches', function (Blueprint $table) {
            $table->unsignedInteger('bracket_position')->nullable()->after('round_number');
            $table->unsignedBigInteger('next_match_id')->nullable()->after('bracket_position');
            $table->index('next_match_id');
        });
    }

    public function down(): void
    {
        Schema::table('open_play_matches', function (Blueprint $table) {
            $table->dropIndex(['next_match_id']);
            $table->dropColumn(['bracket_position', 'next_match_id']);
        });

        Schema::table('court_blocked_slots', function (Blueprint $table) {
            $table->dropColumn('play_format');
        });
    }
};
