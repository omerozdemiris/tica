<?php



namespace App\Mail;



use App\Models\Order;

use App\Models\Setting;

use Illuminate\Bus\Queueable;

use Illuminate\Mail\Mailable;

use Illuminate\Queue\SerializesModels;



class OrderCanceledMail extends Mailable

{

    use Queueable, SerializesModels;



    public Order $order;

    public string $reason;



    public function __construct(Order $order, string $reason)

    {

        $this->order = $order;

        $this->reason = $reason;

    }



    public function build()

    {

        return $this->subject(config('app.name') . ' - Siparişiniz İptal Edildi')

            ->view('emails.orders.canceled')

            ->with([

                'order' => $this->order,

                'reason' => $this->reason,

                'settings' => Setting::first(),

            ]);

    }

}



