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

    /**
     * ERP'den gelen ürün detayları (sku -> ErpProduct).
     *
     * @var \Illuminate\Support\Collection|array|null
     */
    public $erpProducts;



    public function __construct(Order $order, string $status, ?string $message = null, $erpProducts = null)
    {
        $this->order = $order->loadMissing(['items.product', 'items.variant']);
        $this->status = $status;
        $this->message = $message;
        $this->erpProducts = $erpProducts;
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
                'erpProducts' => $this->erpProducts,
            ]);

    }

}
