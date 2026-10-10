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
        Schema::table('users', function (Blueprint $table) {
            $columnsToDrop = [
                'pending_avatar',
                'avatar_status',
                'avatar_rejected_reason',
                'banner',
                'pending_banner',
                'banner_status',
                'banner_rejected_reason',
            ];

            $existingColumns = array_filter($columnsToDrop, fn (string $column) => Schema::hasColumn('users', $column));

            if (! empty($existingColumns)) {
                $table->dropColumn(array_values($existingColumns));
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('pending_avatar')->nullable()->after('avatar');
            $table->enum('avatar_status', ['none', 'pending', 'approved', 'rejected'])->default('none')->after('pending_avatar');
            $table->string('avatar_rejected_reason')->nullable()->after('avatar_status');
            $table->string('banner')->nullable()->after('avatar_rejected_reason');
            $table->string('pending_banner')->nullable()->after('banner');
            $table->enum('banner_status', ['none', 'pending', 'approved', 'rejected'])->default('none')->after('pending_banner');
            $table->string('banner_rejected_reason')->nullable()->after('banner_status');
        });
    }
};
