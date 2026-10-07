<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('court_blocked_slots', function (Blueprint $table) {
            $table->string('poster')->nullable()->after('max_players');
        });
    }

    public function down()
    {
        Schema::table('court_blocked_slots', function (Blueprint $table) {
            $table->dropColumn('poster');
        });
    }
};
