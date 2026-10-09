<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Xuất file sitemap.xml động chuẩn Google cho toàn bộ website Ctrl C+V
     */
    public function index(): Response
    {
        $courses = Course::where('status', 'approved')
            ->orderBy('updated_at', 'desc')
            ->get();

        $baseUrl = config('app.url', 'https://ctrlcv.io.vn');
        $baseUrl = rtrim($baseUrl, '/');

        $latestCourseUpdate = $courses->first()?->updated_at?->toAtomString() ?? now()->toAtomString();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">';

        // 1. Trang chủ
        $xml .= '<url>';
        $xml .= '<loc>'.$baseUrl.'</loc>';
        $xml .= '<lastmod>'.$latestCourseUpdate.'</lastmod>';
        $xml .= '<changefreq>daily</changefreq>';
        $xml .= '<priority>1.0</priority>';
        $xml .= '</url>';

        // 2. Danh mục tất cả khóa học
        $xml .= '<url>';
        $xml .= '<loc>'.$baseUrl.'/courses'.'</loc>';
        $xml .= '<lastmod>'.$latestCourseUpdate.'</lastmod>';
        $xml .= '<changefreq>daily</changefreq>';
        $xml .= '<priority>0.9</priority>';
        $xml .= '</url>';

        // 3. Từng khóa học chi tiết (chuẩn SEO /khoa-hoc/{slug})
        foreach ($courses as $course) {
            $courseUrl = $baseUrl.'/khoa-hoc/'.($course->slug ?: $course->id);
            $lastmod = $course->updated_at ? $course->updated_at->toAtomString() : now()->toAtomString();

            $xml .= '<url>';
            $xml .= '<loc>'.htmlspecialchars($courseUrl, ENT_XML1, 'UTF-8').'</loc>';
            $xml .= '<lastmod>'.$lastmod.'</lastmod>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.8</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
        ]);
    }
}
