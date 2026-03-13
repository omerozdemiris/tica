<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Controller;
use App\Models\AdminRoute;
use App\Models\User;
use App\Services\Logs\AdminLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    protected array $defaultExcludedRoutes = [];

    public function __construct()
    {
        parent::__construct();
        $this->defaultExcludedRoutes = config('admin.excluded_admin_routes', []);
    }

    public function index()
    {
        $users = User::where('role', 1)->orderByDesc('role')->orderBy('name')->get();

        return view('admin.pages.users.index', [
            'users' => $users,
        ]);
    }

    public function create()
    {
        $routes = AdminRoute::orderBy('name')->get();

        return view('admin.pages.users.create', [
            'routes' => $routes,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'special_characters'],
            'email' => ['required', 'string', 'max:255', 'special_characters', 'unique:users,email'],
            'username' => ['required', 'string', 'max:255', 'special_characters', 'unique:users,username'],
            'password' => ['required', 'string', 'min:6', 'special_characters'],
            'role' => ['sometimes', Rule::in([1, 2])],
            'routes' => ['array'],
            'routes.*' => ['integer', 'exists:routes,id'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'] ?? 1,
        ]);

        if ((int) $user->role === 1) {
            $routeIds = $this->mergeWithExcluded($data['routes'] ?? []);
            $user->adminRoutes()->sync($routeIds);
        } else {
            $user->adminRoutes()->detach();
        }

        app(AdminLogService::class)->log('Yönetici Oluşturuldu', null, $user->toArray());

        return redirect()->route('admin.users.index')->with('success', 'Kullanıcı oluşturuldu');
    }

    public function edit(int $id)
    {
        $user = User::whereIn('role', [1, 2])->findOrFail($id);
        $routes = AdminRoute::orderBy('name')->get();
        $selectedRoutes = $user->adminRoutes()->pluck('routes.id')->toArray();

        return view('admin.pages.users.edit', [
            'user' => $user,
            'routes' => $routes,
            'selectedRoutes' => $selectedRoutes,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $user = User::whereIn('role', [1, 2])->findOrFail($id);
        $before = $user->toArray();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'special_characters'],
            'email' => ['required', 'string', 'max:255', 'special_characters', Rule::unique('users', 'email')->ignore($user->id)],
            'username' => ['required', 'string', 'max:255', 'special_characters', Rule::unique('users', 'username')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:6', 'special_characters'],
            'role' => ['sometimes', Rule::in([1, 2])],
            'routes' => ['array'],
            'routes.*' => ['integer', 'exists:routes,id'],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->username = $data['username'];
        $user->phone = $data['phone'] ?? $user->phone;
        $user->role = $data['role'] ?? 1;
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        if ((int) $user->role === 1) {
            $routeIds = $this->mergeWithExcluded($data['routes'] ?? []);
            $user->adminRoutes()->sync($routeIds);
        } else {
            $user->adminRoutes()->detach();
        }

        app(AdminLogService::class)->log('Yönetici Güncellendi', $before, $user->fresh()->toArray());

        return redirect()->route('admin.users.index')->with('success', 'Kullanıcı güncellendi');
    }

    public function destroy(Request $request, int $id)
    {
        $currentId = (int) $request->session()->get('admin_user_id');

        if ($currentId === $id) {
            return back()->withErrors(['msg' => 'Kendi hesabınızı silemezsiniz.']);
        }

        $user = User::whereIn('role', [1, 2])->findOrFail($id);
        $before = $user->toArray();
        $user->adminRoutes()->detach();
        $user->delete();

        app(AdminLogService::class)->log('Yönetici Silindi', $before, null);

        return redirect()->route('admin.users.index')->with('success', 'Kullanıcı silindi');
    }

    private function mergeWithExcluded(array $selectedRouteIds): array
    {
        $excludedIds = $this->ensureExcludedRoutesExist();
        return array_values(array_unique(array_merge($selectedRouteIds, $excludedIds)));
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
