<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('courses', 'banner')) {
            // Đảm bảo an toàn dữ liệu: nếu có khóa học nào có banner mà chưa có thumbnail thì sao chép sang thumbnail
            DB::table('courses')
                ->whereNull('thumbnail')
                ->whereNotNull('banner')
                ->update(['thumbnail' => DB::raw('banner')]);

            Schema::table('courses', function (Blueprint $table) {
                $table->dropColumn('banner');
            });
        }

        if (Schema::hasColumn('users', 'bio')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('bio');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('courses', 'banner')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->string('banner')->nullable()->after('thumbnail');
            });
        }

        if (! Schema::hasColumn('users', 'bio')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('bio')->nullable()->after('avatar');
            });
        }
    }
};
