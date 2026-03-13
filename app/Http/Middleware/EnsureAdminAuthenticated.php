<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $userId = $request->session()->get('admin_user_id');
        $user = $userId ? User::find($userId) : null;

        if (!$request->session()->get('admin_authenticated') || !$user || !$user->isAdmin()) {
            $request->session()->forget(['admin_authenticated', 'admin_user_id']);
            return $this->unauthorized($request);
        }

        return $next($request);
    }

    private function unauthorized(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'code' => 3,
                'msg' => 'Oturum gerekli',
            ], 401);
        }

        return redirect()->to('/admin/login');
    }
}


