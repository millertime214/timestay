<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_feature_slides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image');
            $table->unsignedInteger('serial_number')->default(1);
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->index(['language_id', 'status', 'serial_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_feature_slides');
    }
};
