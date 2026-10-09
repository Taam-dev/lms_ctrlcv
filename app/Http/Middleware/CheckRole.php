<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! auth()->check()) {
            abort(403, 'Bạn chưa đăng nhập.');
        }

        $userRole = auth()->user()->role;

        // Role instructor đã được gộp hoàn toàn vào teacher (Giảng viên)
        $allowedRoles = [];
        foreach ($roles as $role) {
            $allowedRoles[] = ($role === 'instructor') ? 'teacher' : $role;
        }

        if (! in_array($userRole, $allowedRoles, true)) {
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        return $next($request);
    }
}
