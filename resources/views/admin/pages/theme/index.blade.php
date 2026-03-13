@extends('admin.layouts.app')



@push('styles')
    <style>
        [data-theme-preview] {

            --theme-color: #2563eb;

        }



        [data-theme-preview] [data-theme-hover]:hover {

            color: var(--theme-color) !important;

        }
    </style>
@endpush



@section('title', 'Tema Ayarları')



@section('content')


    @php
        $colorKeys = array_keys($colors);
        $defaultColorKey = $colorKeys[0] ?? 'bg-blue-600';

        $storedColor = $theme->color ?: $defaultColorKey;

        $storedColorIsCustom = str_starts_with($storedColor, '[') && str_ends_with($storedColor, ']');
        $storedColorHex = $storedColorIsCustom ? substr($storedColor, 1, -1) : null;

        $normalizeColor = static fn(string $value): string => str_starts_with($value, 'bg-')
            ? substr($value, 3)
            : $value;

        $storedColorSlug = $normalizeColor($storedColor);

        $activeColor = null;

        foreach ($colors as $colorKey => $meta) {
            $isCustom = $meta['custom'] ?? false;

            if ($storedColorIsCustom && $isCustom && isset($meta['hex'])) {
                if ($storedColorHex === $meta['hex']) {
                    $activeColor = $colorKey;
                    break;
                }
            } else {
                if ($storedColorSlug === $normalizeColor($colorKey)) {
                    $activeColor = $colorKey;
                    break;
                }
            }
        }

        $activeColor ??= $defaultColorKey;
        $activeHex = $colors[$activeColor]['hex'] ?? '#2563eb';

        $formatColorLabel = static function (string $value, array $config): string {
            return $config[$value]['label'] ?? ucwords(str_replace('-', ' ', str_replace('bg-', '', $value)));
        };
    @endphp

    <div class="flex items-center justify-between mb-4">

        <h1 class="text-lg font-semibold">Tema Ayarları</h1>

        <span class="text-sm text-gray-500">Aktif renk: <strong
                data-color-label>{{ $formatColorLabel($activeColor, $colors) }}</strong></span>

    </div>



    <div class="grid grid-cols-1 lg:grid-cols-[360px,1fr] gap-6">

        <div class="rounded-lg border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-gray-900">

            <h2 class="font-semibold mb-4">Renk Seçici</h2>

            <form id="theme-form" class="space-y-5" action="{{ route('admin.theme.update') }}" method="POST">

                @csrf

                @method('PUT')

                <div>

                    <label for="theme-color" class="text-sm font-medium text-gray-700 dark:text-gray-200">Renk</label>

                    <div class="mt-2 flex items-center gap-4">

                        <div class="flex-1">
                            <select id="theme-color" name="color"
                                class="w-full rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-800 px-3 py-2 text-sm cursor-pointer focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 dark:focus:ring-gray-700 appearance-none"
                                data-theme-color-select data-tom-select-disabled="true">
                                @foreach ($colors as $class => $meta)
                                    @php
                                        $hex = $meta['hex'];
                                        $label = $formatColorLabel($class, $colors);

                                        $isLight =
                                            str_contains($class, '-50') ||
                                            str_contains($class, '-100') ||
                                            str_contains($class, '-200') ||
                                            str_contains($class, 'white');

                                        $textColor = $isLight ? '#111827' : '#ffffff';
                                    @endphp

                                    <option value="{{ $class }}" data-hex="{{ $hex }}"
                                        data-label="{{ $label }}"
                                        style="background-color: {{ $hex }}; color: {{ $textColor }};"
                                        @selected($class === $activeColor)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <span class="w-12 h-12 rounded-full border border-gray-200 dark:border-gray-700 shadow-inner"
                            data-color-chip style="background-color: {{ $activeHex }};">

                        </span>

                    </div>

                    <p class="mt-2 text-xs text-gray-500">Select içinde yer alan her seçenek arka planında ilgili rengi

                        gösterir.</p>

                </div>



                <div class="flex items-center justify-end gap-2">

                    <button type="submit"
                        class="px-4 py-2 rounded-md text-sm font-semibold border border-gray-200 dark:border-gray-700 bg-black text-white dark:bg-white dark:text-black">

                        Kaydet

                    </button>

                </div>

            </form>

        </div>



        <div class="rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4">

            <div class="flex items-center justify-between mb-4">

                <h2 class="font-semibold">Canlı Önizleme <span><i
                            class="ri-eye-line font-light text-xl opacity-50"></i></span></h2>

                <span class="text-xs uppercase tracking-wide text-gray-500" data-color-label>

                    {{ $formatColorLabel($activeColor, $colors) }}

                </span>

            </div>

            <div class="border border-gray-100 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm" data-theme-preview
                style="--theme-color: {{ $activeHex }};">

                <header class="bg-white shadow-sm">

                    <div class="border-b bg-gray-50" data-color-preview-border>

                        <div class="px-4 sm:px-6 lg:px-8 flex items-center justify-between h-10 text-xs text-gray-600">

                            <div class="flex items-center gap-4">

                                <span class="inline-flex items-center gap-1">

                                    <i class="ri-phone-line" data-color-preview-text></i>

                                    <a href="#" class="hover:text-gray-900" data-color-preview-text>+90 553 000 00

                                        00</a>

                                </span>

                                <span class="inline-flex items-center gap-1">

                                    <i class="ri-mail-line" data-color-preview-text></i>

                                    <a href="#" data-color-preview-text>destek@mağaza.com</a>

                                </span>

                            </div>

                            <div class="flex items-center gap-3">

                                <a href="#" class="inline-flex items-center gap-1 font-semibold"
                                    data-color-preview-text>

                                    <i class="ri-loop-left-line"></i>

                                    Sipariş Sorgula

                                </a>

                                <p class="hover:text-gray-900 font-bold" data-color-preview-text>

                                    <span><i class="ri-user-line mx-1" data-color-preview-text></i></span>

                                    Demo Kullanıcı

                                </p>

                                <a href="#" class="hover:text-gray-900" data-color-preview-text>Hesabım</a>

                                <button type="button" class="hover:text-gray-900" data-color-preview-text>Çıkış
                                    Yap</button>

                            </div>

                        </div>

                    </div>

                    <div class="px-4 sm:px-6 lg:px-8">

                        <div class="flex items-center justify-between h-20">

                            <div class="flex items-center gap-6">

                                <a href="#" class="flex items-center gap-3">

                                    <span class="text-xl font-bold">Mağazanız</span>

                                </a>

                                <nav class="hidden lg:flex items-center gap-4 text-sm font-medium text-gray-700">

                                    <a href="#" class="inline-flex items-center gap-1 transition"
                                        data-color-preview-text>

                                        Anasayfa

                                    </a>

                                    <a href="#" class="inline-flex items-center gap-1 transition"
                                        data-color-preview-text>

                                        Tüm Ürünler

                                    </a>

                                    <div class="relative group px-2 py-1 rounded-xl border" data-color-preview-soft-bg
                                        data-color-preview-border>

                                        <a href="#" class="inline-flex items-center gap-1 font-normal transition"
                                            data-color-preview-text>

                                            Elektronik

                                            <i class="ri-arrow-down-s-line text-xs"></i>

                                        </a>

                                    </div>

                                    <div class="relative group px-2 py-1 rounded-xl border" data-color-preview-soft-bg
                                        data-color-preview-border>

                                        <a href="#" class="inline-flex items-center gap-1 font-normal transition"
                                            data-color-preview-text>

                                            Moda

                                            <i class="ri-arrow-down-s-line text-xs"></i>

                                        </a>

                                    </div>

                                </nav>

                            </div>

                            <div class="flex items-center gap-4">

                                <form action="#" method="GET"
                                    class="hidden md:flex items-center border border-gray-200 rounded-full overflow-hidden">

                                    <input type="text" name="q" placeholder="Ürün ara..."
                                        class="px-4 py-2 outline-none text-sm">

                                    <button type="button" class="px-4 py-2 text-white text-sm hover:opacity-90 transition"
                                        data-color-preview-bg>

                                        <i class="ri-search-line"></i>

                                    </button>

                                </form>

                                <a href="#"
                                    class="relative inline-flex items-center gap-2 px-4 py-2 rounded-full border border-gray-200 hover:bg-gray-100 transition">

                                    <i class="ri-shopping-cart-line text-lg" data-color-preview-text></i>

                                    <span class="text-sm font-medium" data-color-preview-text>Sepet</span>

                                    <span data-cart-count
                                        class="absolute -top-2 -right-2 text-white text-xs w-6 h-6 flex items-center justify-center rounded-full"
                                        data-color-preview-badge>

                                        3

                                    </span>

                                </a>

                            </div>

                        </div>

                    </div>

                </header>

            </div>

        </div>

    </div>



@endsection



@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const select = document.querySelector('[data-theme-color-select]');

            if (!select) {

                return;

            }



            const colorLabels = document.querySelectorAll('[data-color-label]');

            const colorChip = document.querySelector('[data-color-chip]');

            const colorTextTargets = document.querySelectorAll('[data-color-preview-text]');

            const borderTargets = document.querySelectorAll('[data-color-preview-border]');

            const softBgTargets = document.querySelectorAll('[data-color-preview-soft-bg]');

            const solidBgTargets = document.querySelectorAll('[data-color-preview-bg]');

            const badgeTargets = document.querySelectorAll('[data-color-preview-badge]');

            const previewShell = document.querySelector('[data-theme-preview]');



            const hexToRgb = (hex) => {

                const normalized = hex.replace('#', '');

                const bigint = parseInt(normalized, 16);

                return {

                    r: (bigint >> 16) & 255,

                    g: (bigint >> 8) & 255,

                    b: bigint & 255,

                };

            };



            const withAlpha = (hex, alpha) => {

                if (!hex.startsWith('#')) {

                    return hex;

                }

                const {
                    r,
                    g,
                    b
                } = hexToRgb(hex);

                return `rgba(${r}, ${g}, ${b}, ${alpha})`;

            };



            const updatePreview = (value, label, hex) => {

                colorLabels.forEach((element) => {

                    element.textContent = label;

                });



                if (colorChip) {

                    colorChip.style.backgroundColor = hex;

                }



                colorTextTargets.forEach((element) => {

                    element.style.color = hex;

                });



                const borderColor = withAlpha(hex, 0.3);

                borderTargets.forEach((element) => {

                    element.style.borderColor = borderColor;

                });



                const softColor = withAlpha(hex, 0.05);

                softBgTargets.forEach((element) => {

                    element.style.backgroundColor = softColor;

                });



                solidBgTargets.forEach((element) => {

                    element.style.backgroundColor = hex;

                });



                badgeTargets.forEach((element) => {

                    element.style.backgroundColor = hex;

                });



                if (previewShell) {

                    previewShell.style.setProperty('--theme-color', hex);

                }

            };



            select.addEventListener('change', (event) => {

                const option = event.target.selectedOptions[0];



                updatePreview(

                    event.target.value,

                    option.dataset.label ?? event.target.value,

                    option.dataset.hex ?? '#2563eb',

                );

            });



            const initialOption = select.selectedOptions[0];

            if (initialOption) {

                updatePreview(

                    initialOption.value,

                    initialOption.dataset.label ?? initialOption.value,

                    initialOption.dataset.hex ?? '#2563eb',

                );

            }



            const themeForm = document.getElementById('theme-form');

            if (themeForm) {

                const submitButton = themeForm.querySelector('button[type="submit"]');

                const toggleButtonState = (state) => {

                    if (!submitButton) {

                        return;

                    }

                    if (state) {

                        submitButton.dataset.originalText = submitButton.textContent;

                        submitButton.textContent = 'Kaydediliyor...';

                        submitButton.disabled = true;

                        submitButton.classList.add('opacity-75', 'pointer-events-none');

                    } else {

                        submitButton.textContent = submitButton.dataset.originalText || 'Kaydet';

                        submitButton.disabled = false;

                        submitButton.classList.remove('opacity-75', 'pointer-events-none');

                    }

                };



                themeForm.addEventListener('submit', (event) => {

                    event.preventDefault();



                    const formData = new FormData(themeForm);

                    toggleButtonState(true);



                    fetch(themeForm.action, {

                            method: 'POST',

                            headers: {

                                'X-Requested-With': 'XMLHttpRequest',

                            },

                            body: formData,

                        })

                        .then(async (response) => {

                            const data = await response.json().catch(() => ({}));

                            if (response.ok) {

                                window.showSuccess?.(data?.msg || 'Tema ayarları güncellendi');

                            } else {

                                const errorMessage = data?.msg || 'Tema ayarları güncellenemedi.';

                                window.showError?.(errorMessage);

                            }

                        })

                        .catch(() => {

                            window.showError?.('Tema ayarları güncellenemedi.');

                        })

                        .finally(() => {

                            toggleButtonState(false);

                        });

                });

            }

        });
    </script>
@endpush
