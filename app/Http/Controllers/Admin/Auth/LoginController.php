<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Setting;
use App\Models\Store;
use App\Services\Logs\AdminLogService;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        $settings = Setting::first();
        $store = Store::first();
        return view('admin.pages.auth.login', [
            'settings' => $settings,
            'store' => $store,
        ]);
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|string|special_characters',
            'password' => 'required|string|special_characters',
        ]);
        $identifier = $request->string('email')->toString();
        $password = $request->string('password')->toString();

        if (!$identifier || !$password) {
            return back()->withErrors(['email' => 'Geçersiz bilgiler'])->withInput();
        }

        $user = User::where('email', $identifier)
            ->orWhere('username', $identifier)
            ->first();

        if (!$user || !Hash::check($password, $user->password) || !$user->isAdmin()) {
            return back()->withErrors(['email' => 'Kimlik doğrulama başarısız'])->withInput();
        }

        $request->session()->put('admin_authenticated', true);
        $request->session()->put('admin_user_id', $user->id);

        app(AdminLogService::class)->log('Yönetici Girişi Yapıldı');

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        app(AdminLogService::class)->log('Yönetici Çıkışı Yapıldı');

        $request->session()->forget('admin_authenticated');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.auth.login');
    }

    public function deny(Request $request)
    {
        return view('admin.pages.deny');
    }
}
