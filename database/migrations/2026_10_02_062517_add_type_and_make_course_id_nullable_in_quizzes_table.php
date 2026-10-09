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
        Schema::table('quizzes', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['course_id']);
            }
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->unsignedBigInteger('course_id')->nullable()->change();
            if (DB::getDriverName() !== 'sqlite') {
                $table->foreign('course_id')->references('id')->on('courses')->nullOnDelete();
            }
            $table->string('type', 20)->default('quiz')->after('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn('type');
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['course_id']);
            }
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->unsignedBigInteger('course_id')->nullable(false)->change();
            if (DB::getDriverName() !== 'sqlite') {
                $table->foreign('course_id')->references('id')->on('courses')->cascadeOnDelete();
            }
        });
    }
};
