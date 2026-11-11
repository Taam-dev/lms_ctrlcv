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
        Schema::table('minigame_rooms', function (Blueprint $table) {
            if (! Schema::hasColumn('minigame_rooms', 'game_type')) {
                $table->string('game_type')->default('gold_quest')->after('pin_code');
            }
            if (! Schema::hasColumn('minigame_rooms', 'settings')) {
                $table->json('settings')->nullable()->after('duration_minutes');
            }
        });

        Schema::table('minigame_participants', function (Blueprint $table) {
            if (! Schema::hasColumn('minigame_participants', 'session_id')) {
                $table->string('session_id', 64)->nullable()->index()->after('user_id');
            }
            if (! Schema::hasColumn('minigame_participants', 'blook')) {
                $table->string('blook', 50)->default('dragon')->after('nickname');
            }
            if (! Schema::hasColumn('minigame_participants', 'chests_opened')) {
                $table->unsignedInteger('chests_opened')->default(0)->after('wrong_count');
            }
            if (! Schema::hasColumn('minigame_participants', 'final_rank')) {
                $table->unsignedInteger('final_rank')->default(0)->after('chests_opened');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('minigame_participants', function (Blueprint $table) {
            $table->dropColumn(['session_id', 'blook', 'chests_opened', 'final_rank']);
        });

        Schema::table('minigame_rooms', function (Blueprint $table) {
            $table->dropColumn(['game_type', 'settings']);
        });
    }
};
