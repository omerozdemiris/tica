<?php

namespace App\Support;

class TailwindColor
{
    protected const DEFAULT_COLOR = '#2563eb'; // blue-600

    protected static array $basePalette = [
        'slate' => '#64748b',
        'gray' => '#6b7280',
        'zinc' => '#71717a',
        'neutral' => '#737373',
        'stone' => '#78716c',
        'red' => '#ef4444',
        'orange' => '#f97316',
        'amber' => '#f59e0b',
        'yellow' => '#eab308',
        'lime' => '#84cc16',
        'green' => '#22c55e',
        'emerald' => '#10b981',
        'teal' => '#14b8a6',
        'cyan' => '#06b6d4',
        'sky' => '#0ea5e9',
        'blue' => '#3b82f6',
        'indigo' => '#6366f1',
        'violet' => '#8b5cf6',
        'purple' => '#a855f7',
        'fuchsia' => '#d946ef',
        'pink' => '#ec4899',
        'rose' => '#f43f5e',
    ];

    protected static array $shadeAdjustments = [
        '50' => 0.92,
        '100' => 0.8,
        '200' => 0.65,
        '300' => 0.5,
        '400' => 0.25,
        '500' => 0,
        '600' => 0.12,
        '700' => 0.27,
        '800' => 0.42,
        '900' => 0.58,
    ];

    public static function palette(?string $key): array
    {
        $primaryHex = self::hex($key);

        return [
            'primary' => $primaryHex,
            'primary_light' => self::mix($primaryHex, '#ffffff', 0.85),
            'primary_dark' => self::mix($primaryHex, '#000000', 0.35),
            'text_on_primary' => self::contrastText($primaryHex),
        ];
    }

    public static function hex(?string $key): string
    {
        if (!$key) {
            return self::DEFAULT_COLOR;
        }

        [$name, $shade] = array_pad(explode('-', strtolower($key)), 2, null);
        $shade = $shade ?? '600';

        $base = self::$basePalette[$name] ?? self::DEFAULT_COLOR;

        $adjustment = self::$shadeAdjustments[$shade] ?? null;
        if ($shade === '500') {
            return $base;
        }

        if ($adjustment === null) {
            return self::DEFAULT_COLOR;
        }

        return (int) $shade < 500
            ? self::mix($base, '#ffffff', $adjustment)
            : self::mix($base, '#000000', $adjustment);
    }

    protected static function mix(string $hex, string $mixHex, float $amount): string
    {
        $hex = self::normalizeHex($hex);
        $mixHex = self::normalizeHex($mixHex);

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $mr = hexdec(substr($mixHex, 0, 2));
        $mg = hexdec(substr($mixHex, 2, 2));
        $mb = hexdec(substr($mixHex, 4, 2));

        $newR = (int) round($r * (1 - $amount) + $mr * $amount);
        $newG = (int) round($g * (1 - $amount) + $mg * $amount);
        $newB = (int) round($b * (1 - $amount) + $mb * $amount);

        return sprintf('#%02x%02x%02x', $newR, $newG, $newB);
    }

    protected static function normalizeHex(string $hex): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = "{$hex[0]}{$hex[0]}{$hex[1]}{$hex[1]}{$hex[2]}{$hex[2]}";
        }

        return strtolower($hex);
    }

    protected static function contrastText(string $hex): string
    {
        $hex = self::normalizeHex($hex);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

        return $yiq >= 150 ? '#0f172a' : '#ffffff';
    }
}
