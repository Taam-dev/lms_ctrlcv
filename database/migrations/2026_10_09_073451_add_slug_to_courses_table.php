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
        Schema::table('courses', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('title');
        });

        // Tự động sinh slug chuẩn SEO cho các khóa học hiện có
        $courses = DB::table('courses')->orderBy('id')->get();
        $usedSlugs = [];

        foreach ($courses as $course) {
            $baseSlug = Str::slug($course->title) ?: 'khoa-hoc-'.$course->id;
            $slug = $baseSlug;
            $counter = 1;

            while (in_array($slug, $usedSlugs, true)) {
                $slug = $baseSlug.'-'.$counter;
                $counter++;
            }

            $usedSlugs[] = $slug;

            DB::table('courses')->where('id', $course->id)->update([
                'slug' => $slug,
            ]);
        }

        Schema::table('courses', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
