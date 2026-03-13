<?php

namespace App\Services\Erp;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class CustomerService
{
    public function __construct(
        protected ErpApiClient $client
    ) {
    }

    public function createFromOrder(Order $order): ?int
    {
        if (!config('erp.enabled') || !config('erp.orders_enabled')) {
            return null;
        }

        $order->loadMissing(['shippingAddress', 'billingAddress', 'user']);

        $address = $order->billingAddress ?? $order->shippingAddress;
        $name = $order->customer_name
            ?? $address?->fullname
            ?? $order->user?->name
            ?? 'Müşteri';

        // Ad / soyadı kaba şekilde ayır
        $firstName = $name;
        $lastName = '';
        if (str_contains($name, ' ')) {
            $parts = preg_split('/\s+/', $name);
            $firstName = array_shift($parts) ?: $name;
            $lastName = implode(' ', $parts);
        }

        $identityNumber = $address?->tc ?: '11111111111';

        $payload = [
            'branch_id' => 5453,
            'type' => 'customer',
            'customer_legal_type' => 'individual',
            'first_name' => $firstName,
            'last_name' => $lastName ?: $firstName,
            'identity_number' => (string) $identityNumber,
            'email' => $order->customer_email ?? $order->user?->email ?? 'info@example.com',
            'phone' => $order->customer_phone ?? $address?->phone ?? '0000000000',
            'address' => $address?->address ?? '',
            'city' => $address?->city ?? 'İstanbul',
            'district' => $address?->state ?? 'Merkez',
            'postal_code' => $address?->zip ?? '',
            'is_active' => true,
            'currency_preference_for_calculations' => 'buying_rate',
            'default_due_days' => 30,
            'critical_balance_threshold' => 0,
            'send_notifications_on_critical_balance' => false,
            'group_ids' => [],
        ];

        try {
            $response = $this->client->post('/api/company/customers', $payload);

            $customerId = (int) ($response['data']['id'] ?? 0);
            if ($customerId <= 0) {
                Log::warning('ERP müşterisi oluşturuldu ancak ID dönmedi', [
                    'order_id' => $order->id,
                    'response' => $response,
                ]);
                return null;
            }

            Log::info('ERP müşterisi oluşturuldu', [
                'order_id' => $order->id,
                'customer_id' => $customerId,
                'payload' => $payload,
            ]);

            return $customerId;
        } catch (\Throwable $e) {
            Log::warning('ERP müşterisi oluşturulamadı', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
