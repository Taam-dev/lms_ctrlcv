<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('title');
        });

        // Tự động sinh slug chuẩn SEO cho các bài học hiện có
        $lessons = DB::table('lessons')->orderBy('id')->get();
        $usedCourseSlugs = [];

        foreach ($lessons as $lesson) {
            $courseId = $lesson->course_id;
            $baseSlug = Str::slug($lesson->title) ?: 'bai-hoc-'.$lesson->id;
            $slug = $baseSlug;
            $counter = 1;

            if (! isset($usedCourseSlugs[$courseId])) {
                $usedCourseSlugs[$courseId] = [];
            }

            while (in_array($slug, $usedCourseSlugs[$courseId], true)) {
                $slug = $baseSlug.'-'.$counter;
                $counter++;
            }

            $usedCourseSlugs[$courseId][] = $slug;

            DB::table('lessons')->where('id', $lesson->id)->update([
                'slug' => $slug,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
