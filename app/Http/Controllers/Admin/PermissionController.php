<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Controller;
use App\Models\AdminRoute;
use App\Models\User;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    protected array $defaultExcludedRoutes = [];

    public function __construct()
    {
        parent::__construct();
        $this->defaultExcludedRoutes = config('admin.excluded_admin_routes', []);
    }

    public function index(Request $request)
    {
        $users = User::where('role', 1)->orderBy('name')->get();
        $routes = AdminRoute::whereNotIn('name', $this->defaultExcludedRoutes)->orderBy('name')->get();
        $routes = AdminRoute::whereNotIn('name', $this->defaultExcludedRoutes)->orderBy('name')->get();

        $selectedUserId = $request->query('user_id') ? (int) $request->query('user_id') : null;
        $selectedUser = $selectedUserId ? $users->firstWhere('id', $selectedUserId) : $users->first();
        $selectedRoutes = $selectedUser ? $selectedUser->adminRoutes()->pluck('routes.id')->toArray() : [];

        return view('admin.pages.permissions.index', [
            'users' => $users,
            'routes' => $routes,
            'selectedUser' => $selectedUser,
            'selectedRoutes' => $selectedRoutes,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'routes' => ['array'],
            'routes.*' => ['integer', 'exists:routes,id'],
        ]);

        $user = User::where('role', 1)->findOrFail($data['user_id']);
        $excluded = $this->ensureExcludedRoutesExist();
        $routeIds = array_values(array_unique(array_merge($data['routes'] ?? [], $excluded)));
        $user->adminRoutes()->sync($routeIds);

        return redirect()->route('admin.permissions.index', ['user_id' => $user->id])->with('success', 'İzinler güncellendi');
    }

    private function ensureExcludedRoutesExist(): array
    {
        if (empty($this->defaultExcludedRoutes)) {
            return [];
        }

        foreach ($this->defaultExcludedRoutes as $name) {
            AdminRoute::firstOrCreate(['name' => $name], ['uri' => null, 'method' => null]);
        }

        return AdminRoute::whereIn('name', $this->defaultExcludedRoutes)->pluck('id')->toArray();
    }
}
