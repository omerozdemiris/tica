<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Log;
use Iyzipay\Model\Address;
use Iyzipay\Options;
use Iyzipay\Model\Buyer;
use Iyzipay\Model\BasketItem;
use Iyzipay\Model\BasketItemType;
use Iyzipay\Model\CheckoutForm;
use Iyzipay\Model\CheckoutFormInitialize;
use Iyzipay\Model\Currency;
use Iyzipay\Model\Locale;
use Iyzipay\Model\PaymentGroup;
use Iyzipay\Request\CreateCheckoutFormInitializeRequest;
use Iyzipay\Request\RetrieveCheckoutFormRequest;
use RuntimeException;

class IyzicoService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $apiSecret;

    public function __construct()
    {
        $this->baseUrl = 'https://api.iyzipay.com';
        $this->apiKey = 'thtraiJq8dyvPn9thVuxe5KEh013TSsX';
        $this->apiSecret = 'tF9zktfd9m1WPnD7hBPAwvpTVDTCwUSQ';
    }

    /**
     * Iyzico Checkout Form başlatır ve iframe içeriğini döner.
     *
     * @param array $payload
     * @return array{status:string,token:?string,checkoutFormContent:?string,raw:string}
     */
    public function initializeCheckoutForm(array $payload): array
    {
        $this->ensureConfigured();

        $options = $this->buildOptions();

        $conversationId = $payload['conversation_id'];
        $price = number_format((float) $payload['price'], 2, '.', '');
        $paidPrice = number_format((float) $payload['paid_price'], 2, '.', '');
        $currency = strtoupper($payload['currency'] ?? 'TRY');

        $customer = $payload['customer'] ?? [];
        $basketItemsPayload = $payload['basket_items'] ?? [];

        $request = new CreateCheckoutFormInitializeRequest();
        $request->setLocale(Locale::TR);
        $request->setConversationId($conversationId);
        $request->setPrice($price);
        $request->setPaidPrice($paidPrice);
        $request->setCurrency($currency === 'TRY' ? Currency::TL : $currency);
        $request->setBasketId($conversationId);
        $request->setPaymentGroup(PaymentGroup::PRODUCT);
        $request->setCallbackUrl($payload['callback_url']);
        $request->setEnabledInstallments([1]);

        $buyer = new Buyer();
        $buyer->setId($customer['id'] ?? 'BY-' . $conversationId);
        $buyer->setName($customer['name'] ?? 'John');
        $buyer->setSurname($customer['surname'] ?? 'Doe');
        $buyer->setGsmNumber($customer['gsmNumber'] ?? '+905350000000');
        $buyer->setEmail($customer['email'] ?? 'email@example.com');
        $buyer->setIdentityNumber($customer['identityNumber'] ?? '74300864791');
        $buyer->setLastLoginDate(now()->subDay()->format('Y-m-d H:i:s'));
        $buyer->setRegistrationDate(now()->subMonths(3)->format('Y-m-d H:i:s'));
        $buyer->setRegistrationAddress($customer['address'] ?? 'Adres');
        $buyer->setIp(request()->ip() ?? '85.34.78.112');
        $buyer->setCity($customer['city'] ?? 'Istanbul');
        $buyer->setCountry($customer['country'] ?? 'Turkey');
        $buyer->setZipCode($customer['zip'] ?? '34732');
        $request->setBuyer($buyer);

        $shippingAddress = new Address();
        $shippingAddress->setContactName($customer['name'] ?? 'John Doe');
        $shippingAddress->setCity($customer['city'] ?? 'Istanbul');
        $shippingAddress->setCountry($customer['country'] ?? 'Turkey');
        $shippingAddress->setAddress($customer['address'] ?? 'Adres');
        $shippingAddress->setZipCode($customer['zip'] ?? '34742');
        $request->setShippingAddress($shippingAddress);

        $billingAddress = new Address();
        $billingAddress->setContactName($customer['name'] ?? 'John Doe');
        $billingAddress->setCity($customer['city'] ?? 'Istanbul');
        $billingAddress->setCountry($customer['country'] ?? 'Turkey');
        $billingAddress->setAddress($customer['address'] ?? 'Adres');
        $billingAddress->setZipCode($customer['zip'] ?? '34742');
        $request->setBillingAddress($billingAddress);

        $basketItems = [];
        foreach ($basketItemsPayload as $index => $item) {
            $basketItem = new BasketItem();
            $basketItem->setId($item['sku'] ?? 'BI' . (100 + $index));
            $basketItem->setName($item['name'] ?? 'Ürün');
            $basketItem->setCategory1($item['category1'] ?? 'Genel');
            $basketItem->setCategory2($item['category2'] ?? 'Genel');
            $basketItem->setItemType(BasketItemType::PHYSICAL);
            $basketItem->setPrice(number_format((float) ($item['price'] ?? 0), 2, '.', ''));
            $basketItems[] = $basketItem;
        }
        $request->setBasketItems($basketItems);

        $checkoutFormInitialize = CheckoutFormInitialize::create($request, $options);

        if ($checkoutFormInitialize->getStatus() !== 'success') {
            Log::warning('Iyzico checkout form isteği reddedildi', [
                'status' => $checkoutFormInitialize->getStatus(),
                'errorCode' => $checkoutFormInitialize->getErrorCode(),
                'errorMessage' => $checkoutFormInitialize->getErrorMessage(),
                'raw' => $checkoutFormInitialize->getRawResult(),
            ]);

            throw new RuntimeException($checkoutFormInitialize->getErrorMessage() ?: 'Iyzico hata');
        }

        return [
            'status' => $checkoutFormInitialize->getStatus(),
            'token' => $checkoutFormInitialize->getToken(),
            'checkoutFormContent' => $checkoutFormInitialize->getCheckoutFormContent(),
            'raw' => $checkoutFormInitialize->getRawResult(),
        ];
    }


    public function retrievePaymentResult(string $token): array
    {
        $this->ensureConfigured();

        $options = $this->buildOptions();

        $request = new RetrieveCheckoutFormRequest();
        $request->setLocale(Locale::TR);
        $request->setToken($token);

        $checkoutForm = CheckoutForm::retrieve($request, $options);

        if ($checkoutForm->getStatus() !== 'success') {
            Log::warning('Iyzico ödeme sonucu başarısız', [
                'status' => $checkoutForm->getStatus(),
                'errorCode' => $checkoutForm->getErrorCode(),
                'errorMessage' => $checkoutForm->getErrorMessage(),
                'raw' => $checkoutForm->getRawResult(),
            ]);
        }

        return [
            'status' => $checkoutForm->getStatus(),
            'paymentStatus' => $checkoutForm->getPaymentStatus(),
            'basketId' => $checkoutForm->getBasketId(),
            'conversationId' => $checkoutForm->getConversationId(),
            'raw' => $checkoutForm->getRawResult(),
        ];
    }

    protected function buildOptions(): Options
    {
        $options = new Options();
        $options->setApiKey($this->apiKey);
        $options->setSecretKey($this->apiSecret);
        $options->setBaseUrl($this->baseUrl);

        return $options;
    }

    protected function ensureConfigured(): void
    {
        if (!$this->apiKey || !$this->apiSecret) {
            throw new RuntimeException('Iyzico API ayarları eksik.');
        }
    }
}
