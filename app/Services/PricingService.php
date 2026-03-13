<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductAttributeTerm;
use App\Models\Store;

class PricingService
{
    public const TAX_BEHAVIOR_INHERIT = 0;
    public const TAX_BEHAVIOR_EXEMPT = 1;
    public const TAX_BEHAVIOR_CUSTOM = 2;

    protected ?Store $store;

    public function __construct(?Store $store = null)
    {
        $this->store = $store ?? Store::first();
    }

    public function getStore(): ?Store
    {
        return $this->currentStore();
    }

    public function summarizeCart(?Cart $cart): array
    {
        $store = $this->currentStore();
        $summary = [
            'items' => [],
            'totals' => [
                'net' => 0.0,
                'tax' => 0.0,
                'gross' => 0.0,
            ],
            'tax_breakdown' => [],
            'tax_enabled' => (bool) ($store?->tax_enabled),
            'store_tax_rate' => $this->normalizeRate($store?->tax_rate),
        ];

        if (!$cart) {
            return $summary;
        }

        $cart->loadMissing(['items.product', 'items.variant']);
        $items = $cart->items ?? collect();

        foreach ($items as $item) {
            $calc = $this->calculateForAmount(
                (float) $item->subtotal,
                $item->product,
                $item->variant
            );

            $summary['items'][$item->id] = array_merge($calc, [
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->price,
            ]);

            $summary['totals']['net'] += $calc['net'];
            $summary['totals']['tax'] += $calc['tax'];
            $summary['totals']['gross'] += $calc['gross'];

            if ($calc['tax'] > 0 && $calc['tax_rate'] !== null) {
                $key = (string) $calc['tax_rate'];
                if (!isset($summary['tax_breakdown'][$key])) {
                    $summary['tax_breakdown'][$key] = [
                        'rate' => $calc['tax_rate'],
                        'label' => $this->formatRateLabel($calc['tax_rate']),
                        'amount' => 0.0,
                    ];
                }

                $summary['tax_breakdown'][$key]['amount'] += $calc['tax'];
            }
        }

        $summary['totals'] = array_map(
            fn($value) => round($value, 2),
            $summary['totals']
        );

        $summary['tax_breakdown'] = array_values(array_map(function ($line) {
            $line['amount'] = round($line['amount'], 2);
            return $line;
        }, $summary['tax_breakdown']));

        return $summary;
    }

    public function calculateForProduct(Product $product, ?ProductAttributeTerm $variant = null): array
    {
        $price = $variant?->price ?? $product->price ?? 0;

        return $this->calculateForAmount((float) $price, $product, $variant);
    }

    public function calculateForAmount(float $baseAmount, ?Product $product = null, ?ProductAttributeTerm $variant = null): array
    {
        $net = round(max(0, $baseAmount), 2);
        $taxInfo = $this->resolveTax($product, $variant);

        $tax = 0.0;
        $gross = $net;
        $rate = $taxInfo['rate'];

        if ($taxInfo['enabled'] && $rate !== null && $rate > 0) {
            $tax = round($net * ($rate / 100), 2);
            $gross = round($net + $tax, 2);
        }

        return [
            'gross' => $gross,
            'net' => $net,
            'tax' => $tax,
            'tax_rate' => $rate,
            'tax_enabled' => $taxInfo['enabled'],
            'behavior' => $taxInfo['behavior'],
        ];
    }

    protected function resolveTax(?Product $product, ?ProductAttributeTerm $variant): array
    {
        $store = $this->currentStore();
        $storeEnabled = (bool) ($store?->tax_enabled);
        $storeRate = $this->normalizeRate($store?->tax_rate);

        if (!$storeEnabled || $storeRate === null) {
            return [
                'enabled' => false,
                'behavior' => self::TAX_BEHAVIOR_INHERIT,
                'rate' => null,
            ];
        }

        foreach ([$variant, $product] as $target) {
            if (!$target) {
                continue;
            }

            $behavior = (int) ($target->tax_behavior ?? self::TAX_BEHAVIOR_INHERIT);
            $customRate = $this->normalizeRate($target->tax_rate);

            if ($behavior === self::TAX_BEHAVIOR_EXEMPT) {
                return [
                    'enabled' => true,
                    'behavior' => $behavior,
                    'rate' => null,
                ];
            }

            if ($behavior === self::TAX_BEHAVIOR_CUSTOM) {
                return [
                    'enabled' => true,
                    'behavior' => $behavior,
                    'rate' => $customRate,
                ];
            }
        }

        return [
            'enabled' => true,
            'behavior' => self::TAX_BEHAVIOR_INHERIT,
            'rate' => $storeRate,
        ];
    }

    protected function normalizeRate($rate): ?float
    {
        if ($rate === null || $rate === '') {
            return null;
        }

        $value = round((float) $rate, 4);

        return $value >= 0 ? $value : null;
    }

    protected function formatRateLabel(?float $rate): string
    {
        if ($rate === null) {
            return 'Muaf';
        }

        $formatted = rtrim(rtrim(number_format($rate, 2, ',', '.'), '0'), ',');

        return '%' . $formatted;
    }

    protected function currentStore(): ?Store
    {
        if ($this->store?->exists) {
            return $this->store;
        }

        $this->store = Store::query()->latest('id')->first();

        return $this->store;
    }
}
