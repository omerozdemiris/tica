<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function build()
    {
        return $this->subject(config('app.name') . ' - Siparişiniz yola çıktı')
            ->view('emails.orders.completed')
            ->with([
                'order' => $this->order,
                'settings' => Setting::first(),
            ]);
    }
}

