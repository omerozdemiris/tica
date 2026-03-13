<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\AdminRoute;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class AdminRoutePermission
{
    public function handle(Request $request, Closure $next): HttpResponse
    {
        $userId = $request->session()->get('admin_user_id');
        $user = $userId ? User::find($userId) : null;

        if (!$user || !$user->isAdmin()) {
            $request->session()->forget(['admin_authenticated', 'admin_user_id']);
            return $this->deny($request, 'Oturum bilgisi bulunamadı');
        }

        if ($user->isSuperAdmin()) {
            $request->attributes->set('adminUser', $user);
            View::share('adminUser', $user);
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        $group = AdminRoute::groupFromRouteName($routeName);
        $excluded = Config::get('admin.excluded_admin_routes', []);

        if ($group && in_array($group, $excluded, true)) {
            $request->attributes->set('adminUser', $user);
            View::share('adminUser', $user);
            return $next($request);
        }

        $allowed = $group ? $user->hasRouteAccess($routeName) : false;

        if (!$allowed) {
            return $this->deny($request, 'Bu işlem için izniniz yok');
        }

        $request->attributes->set('adminUser', $user);
        View::share('adminUser', $user);
        return $next($request);
    }

    private function deny(Request $request, string $message): HttpResponse
    {
        if ($request->expectsJson()) {
            return Response::json([
                'code' => 3,
                'msg' => $message,
            ], 403);
        }

        return redirect()->route('admin.auth.deny')->withErrors(['email' => $message]);
    }
}

