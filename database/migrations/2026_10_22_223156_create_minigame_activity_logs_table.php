<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('minigame_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('minigame_rooms')->cascadeOnDelete();
            $table->foreignId('participant_id')->nullable()->constrained('minigame_participants')->cascadeOnDelete();
            $table->string('action_type');
            $table->string('message');
            $table->timestamps();

            $table->index(['room_id', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('minigame_activity_logs');
    }
};
