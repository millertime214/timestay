<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('open_play_matches', function (Blueprint $table) {
            $table->unsignedSmallInteger('team_one_score')->nullable()->after('status');
            $table->unsignedSmallInteger('team_two_score')->nullable()->after('team_one_score');
            $table->unsignedTinyInteger('winner_team')->nullable()->after('team_two_score');
        });
    }

    public function down(): void
    {
        Schema::table('open_play_matches', function (Blueprint $table) {
            $table->dropColumn(['team_one_score', 'team_two_score', 'winner_team']);
        });
    }
};
