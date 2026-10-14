<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('open_play_roster_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_blocked_slot_id')->constrained('court_blocked_slots')->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->unsignedInteger('player_index')->default(0);
            $table->string('name');
            $table->string('gender', 40)->nullable();
            $table->string('category', 40)->nullable();
            $table->unsignedTinyInteger('skill_rating')->default(5);
            $table->unsignedInteger('queue_position')->default(0);
            $table->unsignedInteger('matches_played')->default(0);
            $table->unsignedInteger('wait_rounds')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();
            $table->unique(['booking_id', 'player_index'], 'open_play_roster_booking_player_unique');
            $table->index(['court_blocked_slot_id', 'active']);
        });

        Schema::create('open_play_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('court_blocked_slot_id')->constrained('court_blocked_slots')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->unsignedInteger('round_number');
            $table->unsignedInteger('court_number')->default(1);
            $table->string('skill_group', 40)->nullable();
            $table->enum('status', ['scheduled', 'completed'])->default('scheduled');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['court_blocked_slot_id', 'round_number', 'court_number'], 'open_play_round_court_unique');
        });

        Schema::create('open_play_match_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('open_play_match_id')->constrained('open_play_matches')->cascadeOnDelete();
            $table->foreignId('open_play_roster_entry_id')->constrained('open_play_roster_entries')->cascadeOnDelete();
            $table->unsignedTinyInteger('team');
            $table->timestamps();
            $table->unique(['open_play_match_id', 'open_play_roster_entry_id'], 'open_play_match_roster_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('open_play_match_players');
        Schema::dropIfExists('open_play_matches');
        Schema::dropIfExists('open_play_roster_entries');
    }
};
