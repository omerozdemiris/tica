<?php

namespace App\Services\Erp;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use App\Services\Erp\ProductService;
use App\Services\Erp\CustomerService;

class InvoiceService
{
    public function __construct(
        protected ErpApiClient $client,
        protected ProductService $products,
        protected CustomerService $customers
    ) {
    }

    /**
     * Başarılı sipariş için ERP tarafında fatura oluşturmayı dener.
     */
    public function createInvoiceForOrder(Order $order): void
    {
        if (!config('erp.enabled') || !config('erp.orders_enabled')) {
            return;
        }

        $order->loadMissing(['items.product', 'user', 'shippingAddress', 'billingAddress']);

        $now = now();

        $details = [
            'direction' => 'outgoing',
            // EasyTrade enum'larına göre:
            // type: SATIS | IADE | TEVKIFAT | ISTISNA | OZELMATRAH | IHRACKAYITLI | SGK
            // profile: TEMELFATURA | TICARIFATURA | EARSIVFATURA | IHRACAT | YOLCUBERABERFATURA | OZELFATURA | KAMU
            'invoice_type' => 'SATIS',
            'profile' => 'TICARIFATURA',
            'invoice_date' => $now->toDateString(),
            'issue_time' => $now->format('H:i'),
            'due_date' => $now->copy()->addDays(14)->toDateString(),
            'currency' => 'TRY',
            'exchange_rate' => 1,
            'notes' => [],
        ];

        $customerName = $order->customer_name ?? $order->user?->name ?? 'Müşteri';

        // Önce ERP tarafında müşteri/cari oluştur ve ID'sini al
        $customerId = $this->customers->createFromOrder($order);
        if (!$customerId) {
            // Müşteri oluşturulamadıysa faturayı denemeyelim
            Log::warning('ERP faturası için müşteri ID alınamadı', [
                'order_id' => $order->id,
            ]);
            return;
        }

        $customer = [
            'id' => $customerId,
        ];

        $lines = [];
        $erpProductCache = [];

        foreach ($order->items as $item) {
            $product = $item->product;

            $taxRate = 20.0;
            $erpProductId = null;

            try {
                $sku = $product?->code;
                if ($sku) {
                    if (!array_key_exists($sku, $erpProductCache)) {
                        $erpProductCache[$sku] = $this->products->findBySearch($sku);
                    }

                    $erpProduct = $erpProductCache[$sku];

                    if ($erpProduct && isset($erpProduct->raw)) {
                        $raw = $erpProduct->raw;

                        if (isset($raw['retail_tax_rate']['rate'])) {
                            $taxRate = (float) $raw['retail_tax_rate']['rate'];
                        } elseif (isset($raw['account_tax_rate']['rate'])) {
                            $taxRate = (float) $raw['account_tax_rate']['rate'];
                        }

                        if (isset($raw['id'])) {
                            $erpProductId = (int) $raw['id'];
                        } elseif (isset($raw['product_id'])) {
                            $erpProductId = (int) $raw['product_id'];
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('ERP ürün bilgisi okunamadı, varsayılan değerler kullanılacak', [
                    'order_id' => $order->id,
                    'order_item_id' => $item->id,
                    'product_id' => $product?->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $lines[] = [
                'name' => $product?->title ?? 'Ürün',
                'quantity' => (int) $item->quantity,
                'unit_code' => 'C62',
                'unit_price' => (float) $item->price,
                'tax_rate' => $taxRate,
                'product_id' => $erpProductId,
            ];
        }

        $payload = [
            'details' => $details,
            'customer' => $customer,
            'lines' => $lines,
            'meta' => [
                'local_order_id' => $order->id,
                'local_order_number' => $order->order_number,
            ],
        ];

        try {
            $response = $this->client->post('/api/company/invoices', $payload);

            Log::info('ERP faturası oluşturma isteği gönderildi', [
                'order_id' => $order->id,
                'payload' => $payload,
                'response' => $response,
            ]);
        } catch (\Throwable $e) {
            Log::warning('ERP faturası oluşturulamadı', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
