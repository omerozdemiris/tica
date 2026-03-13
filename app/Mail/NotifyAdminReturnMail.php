<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\ReturnRequest;

class NotifyAdminReturnMail extends Mailable
{
    use Queueable, SerializesModels;

    public ReturnRequest $return;
    public string $status;

    public function __construct(ReturnRequest $return, string $status)
    {
        $this->return = $return->loadMissing(['order.items.product', 'items.orderItem.product']);
        $this->status = $status;
    }

    public function build()
    {
        return $this->subject(config('app.name') . ' - Yeni İade Talebi Alındı')
            ->view('emails.orders.notify_return_to_admin')
            ->with([
                'return' => $this->return,
                'order' => $this->return->order,
            ]);
    }
}
