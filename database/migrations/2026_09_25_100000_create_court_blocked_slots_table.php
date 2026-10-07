<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('court_blocked_slots')) {
            Schema::create('court_blocked_slots', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('vendor_id');
                $table->unsignedBigInteger('room_id');
                $table->dateTime('starts_at');
                $table->dateTime('ends_at');
                $table->string('reason')->nullable();
                $table->timestamps();
                $table->index(['room_id', 'starts_at', 'ends_at'], 'court_block_time_idx');
                $table->index(['vendor_id', 'starts_at'], 'vendor_block_start_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('court_blocked_slots');
    }
};
