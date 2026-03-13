<?php

namespace App\Mail;

use App\Models\Theme;
use App\Models\User;
use App\Support\TailwindColor;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $verificationUrl;
    public array $themeColors;
    public Setting $settings;
    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $verificationUrl)
    {
        $this->user = $user;
        $this->verificationUrl = $verificationUrl;
        $this->settings = Setting::first();
        $themeColorKey = optional(Theme::first())->color;
        $this->themeColors = TailwindColor::palette($themeColorKey);
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->subject('E-posta Doğrulama')
            ->view('emails.verification')
            ->with([
                'user' => $this->user,
                'verificationUrl' => $this->verificationUrl,
                'themeColors' => $this->themeColors,
                'settings' => $this->settings,
            ]);
    }
}
