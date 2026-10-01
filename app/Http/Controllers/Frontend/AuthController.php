<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Theme;
use App\Services\SmsService;
use App\Services\CartService;
use App\Services\Logs\CustomerLogService;
use App\Mail\ResetPasswordMail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected CartService $cartService;
    public function __construct(CartService $cartService)
    {
        parent::__construct();
        $this->cartService = $cartService;
    }

    public function showLogin(): View
    {
        $theme = Theme::first();
        $data = (object) [];
        $meta = (object) [
            'title' => 'Giriş Yap | ' . ($this->store?->meta_title ?? config('app.name')),
        ];

        return view($theme->thene . '.pages.auth.login', [
            'data' => $data,
            'meta' => $meta,
        ]);
    }

    public function login(Request $request): RedirectResponse|JsonResponse
    {
        $messages = [
            'email.required' => 'E-posta adresi zorunludur.',
            'email.email' => 'Lütfen geçerli bir e-posta adresi girin.',
            'password.required' => 'Şifre zorunludur.',
        ];

        $attributes = [
            'email' => 'E-posta adresi',
            'password' => 'Şifre',
        ];

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ], $messages, $attributes);

        $remember = $request->boolean('remember');

        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $remember)) {
            $user = Auth::user();

            app(CustomerLogService::class)->log('Müşteri Girişi Yapıldı');

            if (($this->store?->verify_required ?? true) && !$user->hasVerifiedEmail()) {
                Auth::logout();
                $user->sendEmailVerificationNotification();
                throw ValidationException::withMessages([
                    'email' => 'E-posta adresinizi doğrulamalısınız. Gelen kutunuzu kontrol edin.',
                ]);
            }

            $this->syncGuestCart($user);

            $request->session()->regenerate();
            session()->regenerateToken();

            $defaultRedirect = $this->store?->auth_required ? route('user.dashboard') : route('home');
            $redirectResponse = redirect()->intended($defaultRedirect);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Başarıyla giriş yaptınız.',
                    'redirect' => $redirectResponse->getTargetUrl(),
                ]);
            }

            return $redirectResponse->with('status', 'Başarıyla giriş yaptınız.');
        }

        throw ValidationException::withMessages([
            'email' => 'E-posta adresi veya şifre hatalı.',
        ]);
    }

    public function showRegister(): View
    {
        $theme = Theme::first();
        $data = (object) [];
        $meta = (object) [
            'title' => 'Kayıt Ol | ' . ($this->store?->meta_title ?? config('app.name')),
        ];

        return view($theme->thene . '.pages.auth.register', [
            'data' => $data,
            'meta' => $meta,
        ]);
    }

    public function register(Request $request): RedirectResponse|JsonResponse
    {
        $messages = [
            'name.required' => 'Ad soyad alanı zorunludur.',
            'email.required' => 'E-posta adresi zorunludur.',
            'email.email' => 'Lütfen geçerli bir e-posta adresi girin.',
            'email.unique' => 'Bu e-posta adresi ile daha önce kayıt olunmuş.',
            'phone.required' => 'Telefon numarası zorunludur.',
            'password.required' => 'Şifre zorunludur.',
            'password.min' => 'Şifreniz en az :min karakter olmalıdır.',
            'password.confirmed' => 'Şifre tekrarınız eşleşmiyor.',
        ];

        $attributes = [
            'name' => 'Ad soyad',
            'email' => 'E-posta adresi',
            'phone' => 'Telefon numarası',
            'password' => 'Şifre',
        ];


        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => [$this->store?->phone_required ? 'required' : 'nullable', 'special_characters'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], $messages, $attributes);



        $guestCartId = $this->cartService->currentGuestId(false);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 0,
            'guest_id' => $guestCartId,
        ]);

        app(CustomerLogService::class)->log('Müşteri Kaydı Tamamlandı', null, $user->toArray());

        if ($this->store?->verify_required ?? true) {
            event(new Registered($user));
            return $this->verificationRequiredResponse($request);
        }

        $user->markEmailAsVerified();
        event(new Registered($user));

        Auth::login($user);
        $this->syncGuestCart($user);

        $request->session()->regenerate();

        $defaultRedirect = $this->store?->auth_required ? route('user.dashboard') : route('home');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Kayıt işleminiz başarıyla tamamlandı.',
                'redirect' => $defaultRedirect,
            ]);
        }

        return redirect()->intended($defaultRedirect)->with('status', 'Kayıt işleminiz başarıyla tamamlandı.');
    }

    public function logout(Request $request): RedirectResponse
    {
        app(CustomerLogService::class)->log('Müşteri Çıkışı Yapıldı');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'Oturumunuz kapatıldı.');
    }

    public function showForgotPassword(): View
    {
        $theme = Theme::first();
        $meta = (object) [
            'title' => 'Şifremi Unuttum | ' . ($this->store?->meta_title ?? config('app.name')),
        ];

        return view($theme->thene . '.pages.auth.forgot-password', compact('meta'));
    }

    public function searchUserByEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['success' => false, 'msg' => 'Bu e-posta adresi ile kayıtlı bir kullanıcı bulunamadı.']);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'initials' => $user->initials,
            ]
        ]);
    }

    public function sendResetLink(Request $request): JsonResponse
    {
        $via = $request->input('via', 'email');

        if ($via === 'phone') {
            $request->validate([
                'phone' => ['required', 'special_characters'],
            ]);

            $user = User::where('phone', $request->phone)->first();

            if (!$user) {
                return response()->json(['success' => false, 'msg' => 'Bu telefon numarası ile kayıtlı bir kullanıcı bulunamadı.']);
            }

            $sessionKey = 'password_reset_last_sent_phone_' . $user->id;
            $lastSent = session($sessionKey);
            if ($lastSent) {
                $elapsed = Carbon::parse($lastSent)->diffInSeconds(now());
                if ($elapsed < 60) {
                    $remaining = 60 - $elapsed;
                    return response()->json([
                        'success' => false,
                        'msg' => 'Lütfen ' . $remaining . ' saniye sonra tekrar deneyin.',
                        'cooldown' => true,
                        'remaining' => $remaining,
                    ]);
                }
            }

            $token = Str::random(64);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                ['token' => Hash::make($token), 'created_at' => Carbon::now()]
            );

            $resetUrl = route('password.reset', $token);
            $message = "Şifre sıfırlama bağlantınız: " . $resetUrl;

            (new SmsService())->sendSms($user->phone, $message);

            session([$sessionKey => now()]);

            return response()->json([
                'success' => true,
                'msg' => 'Şifre sıfırlama bağlantısı telefonunuza SMS olarak gönderildi.',
                'cooldown' => true,
                'remaining' => 60,
            ]);
        }

        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['success' => false, 'msg' => 'Kullanıcı bulunamadı.']);
        }

        $sessionKey = 'password_reset_last_sent_email_' . $user->id;
        $lastSent = session($sessionKey);
        if ($lastSent) {
            $elapsed = Carbon::parse($lastSent)->diffInSeconds(now());
            if ($elapsed < 60) {
                $remaining = 60 - $elapsed;
                return response()->json([
                    'success' => false,
                    'msg' => 'Lütfen ' . $remaining . ' saniye sonra tekrar deneyin.',
                    'cooldown' => true,
                    'remaining' => $remaining,
                ]);
            }
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => Hash::make($token), 'created_at' => Carbon::now()]
        );

        Mail::to($request->email)->send(new ResetPasswordMail($token, $user));

        session([$sessionKey => now()]);

        return response()->json([
            'success' => true,
            'msg' => 'Şifre sıfırlama bağlantısı e-posta adresinize gönderildi.',
            'cooldown' => true,
            'remaining' => 60,
        ]);
    }

    public function showResetPassword($token): View
    {
        $theme = Theme::first();
        $meta = (object) [
            'title' => 'Yeni Şifre Oluştur | ' . ($this->store?->meta_title ?? config('app.name')),
        ];

        return view($theme->thene . '.pages.auth.reset-password', compact('token', 'meta'));
    }

    public function resetPassword(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'token' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $resetRecords = DB::table('password_reset_tokens')->get();
        $reset = $resetRecords->first(function ($record) use ($request) {
            return Hash::check($request->token, $record->token);
        });

        if (!$reset) {
            return response()->json([
                'success' => false,
                'msg' => 'Geçersiz veya süresi dolmuş bağlantı.',
            ]);
        }

        $user = User::where('email', $reset->email)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'msg' => 'Kullanıcı bulunamadı.',
            ]);
        }

        $user->update(['password' => Hash::make($request->password)]);

        app(CustomerLogService::class)->log('Şifre Sıfırlandı', null, ['user_id' => $user->id, 'email' => $user->email]);

        DB::table('password_reset_tokens')->where('email', $reset->email)->delete();

        Auth::login($user);

        return response()->json([
            'success' => true,
            'msg' => 'Şifreniz başarıyla güncellendi, giriş yapılıyor...',
            'redirect' => route('user.dashboard')
        ]);
    }

    protected function syncGuestCart(?User $user): void
    {
        if (!$user) {
            return;
        }

        $sessionGuestId = $this->cartService->currentGuestId(false);
        $guestId = $sessionGuestId ?: $user->guest_id;

        if (!$guestId) {
            return;
        }

        $this->cartService->transferGuestCartToUser($guestId, $user->id);

        if ($user->guest_id) {
            $user->guest_id = null;
            $user->save();
        }
    }

    protected function verificationRequiredResponse(Request $request): RedirectResponse|JsonResponse
    {
        $message = 'Kayıt işlemi tamamlandı. E-posta adresinize doğrulama bağlantısı gönderildi.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'redirect' => route('verification.notice'),
            ]);
        }

        return redirect()->route('verification.notice')->with('status', $message);
    }
}
