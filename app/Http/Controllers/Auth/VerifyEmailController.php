<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerifyEmailController extends Controller
{
    public function notice(): View
    {
        return view('frontend.pages.auth.verify-notice');
    }

    public function resend(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.required' => 'E-posta adresi zorunludur.',
            'email.email' => 'Geçerli bir e-posta adresi girin.',
            'email.exists' => 'Bu e-posta adresi ile kayıt bulunamadı.',
        ]);

        $user = User::where('email', $validated['email'])->firstOrFail();

        if ($user->hasVerifiedEmail()) {
            return back()->with('status', 'Hesabınız zaten doğrulanmış. Giriş yapabilirsiniz.');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('status', 'Doğrulama bağlantısı e-posta adresinize yeniden gönderildi.');
    }

    public function verify(Request $request, int $id, string $hash): RedirectResponse
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Geçersiz doğrulama bağlantısı.');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('login')->with('status', 'E-posta adresiniz zaten doğrulanmış. Lütfen giriş yapın.');
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return redirect()->route('login')->with('status', 'E-posta adresiniz doğrulandı. Şimdi giriş yapabilirsiniz.');
    }
}
