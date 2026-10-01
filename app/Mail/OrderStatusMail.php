<?php



namespace App\Mail;



use App\Models\Order;

use App\Models\Bank;

use App\Models\Setting;

use Illuminate\Bus\Queueable;

use Illuminate\Mail\Mailable;

use Illuminate\Queue\SerializesModels;



class OrderStatusMail extends Mailable

{

    use Queueable, SerializesModels;



    public Order $order;

    public string $status;

    public ?string $message;



    public function __construct(Order $order, string $status, ?string $message = null)

    {

        $this->order = $order;

        $this->status = $status;

        $this->message = $message;

    }



    public function build()

    {

        $statusLabels = [

            'new' => 'Siparişiniz Oluşturuldu',

            'pending' => 'Siparişiniz Hazırlanıyor',

            'completed' => 'Siparişiniz Yola Çıktı',

            'canceled' => 'Siparişiniz İptal Edildi',

        ];



        $subject = config('app.name') . ' - ' . ($statusLabels[$this->status] ?? 'Sipariş Durumu Güncellendi');



        $wireBanks = collect();

        if ($this->order->method === 'wire') {

            $wireBanks = Bank::active()->get();

        }



        return $this->subject($subject)

            ->view('emails.orders.status')

            ->with([

                'order' => $this->order,

                'status' => $this->status,

                'messageContent' => $this->message,

                'wireBanks' => $wireBanks,

                'settings' => Setting::first(),

            ]);

    }

}
