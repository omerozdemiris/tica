<?php



namespace App\Mail;



use App\Models\Order;
use App\Models\ReturnRequest;

use App\Models\Bank;

use App\Models\Setting;

use Illuminate\Bus\Queueable;

use Illuminate\Mail\Mailable;

use Illuminate\Queue\SerializesModels;



class ReturnStatusMail extends Mailable

{

    use Queueable, SerializesModels;



    public ReturnRequest $return;

    public string $status;

    public ?string $message;



    public function __construct(ReturnRequest $return, string $status, ?string $message = null)

    {

        $this->return = $return;

        $this->status = $status;

        $this->message = $message;
    }



    public function build()

    {

        $statusLabels = [
            'pending' => 'İade Talebiniz Alındı',
            'processed' => 'İade Talebiniz Onaylandı',
            'rejected' => 'İade Talebiniz Reddedildi',
        ];



        $subject = config('app.name') . ' - ' . ($statusLabels[$this->status] ?? 'Sipariş Durumu Güncellendi');



        $wireBanks = collect();

        if ($this->return->order->method === 'wire') {

            $wireBanks = Bank::active()->get();
        }



        return $this->subject($subject)

            ->view('emails.orders.return_status')

            ->with([

                'return' => $this->return,

                'status' => $this->status,

                'messageContent' => $this->message,

                'wireBanks' => $wireBanks,

                'settings' => Setting::first(),

            ]);
    }
}
