<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotifyAdminOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order->loadMissing(['items.product', 'items.variant']);
    }

    public function build()
    {
        return $this->subject(config('app.name') . ' - Yeni Sipariş Alındı')
            ->view('emails.orders.notifytoadmin')
            ->with([
                'order' => $this->order,
            ]);
    }
}
