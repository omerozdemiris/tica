@extends('admin.layouts.app')
@section('title', 'Ürün Düzenle')
@section('content')
    @php
        $currentStore = $store ?? \App\Models\Store::first();
        $storeTaxConfig = [
            'enabled' => (bool) ($currentStore->tax_enabled ?? false),
            'rate' => (float) ($currentStore->tax_rate ?? 0),
        ];
        $hasVariants = $product->variants && $product->variants->count() > 0;
    @endphp
    <h1 class="text-lg font-semibold mb-4 text-gray-800">Ürünü Düzenle</h1>

    <form id="product-edit" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div
            class="bg-white dark:bg-black/20 p-8 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 space-y-8">
            <!-- 1. Satır: Başlık, Kategoriler, Fotoğraf -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1 block">Ürün Başlığı</label>
                    <div class="relative">
                        <i class="ri-text absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="title" value="{{ $product->title }}"
                            class="pl-9 w-full px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-black text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1 block">Kategoriler</label>
                    <select name="category_ids[]" class="js-select w-full" multiple>
                        @foreach ($categories ?? [] as $cat)
                            <option value="{{ $cat->id }}" @if ($product->categories->contains($cat->id)) selected @endif>
                                {{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1 block">Ürün
                        Fotoğrafı</label>
                    <div class="flex items-center gap-3">
                        <label
                            class="flex-1 flex items-center justify-between gap-3 bg-gray-50/50 rounded-xl border border-dashed border-gray-300 dark:border-gray-700 px-3 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-900 transition-colors">
                            <div class="flex items-center gap-3">
                                <i class="ri-image-line text-xl text-gray-400"></i>
                                <span class="text-xs text-gray-500" data-file-label
                                    data-default-text="Yeni fotoğraf seçin...">Yeni fotoğraf seçin...</span>
                            </div>
                            <span
                                class="text-[10px] px-2 py-1 bg-white dark:bg-black border border-gray-200 dark:border-gray-800 rounded-lg shadow-sm">Gözat</span>
                            <input type="file" name="photo_file" accept="image/*" class="sr-only" data-file-input>
                        </label>
                        @if ($product->photo)
                            <div class="relative inline-block w-14 h-14 group shrink-0">
                                <img src="{{ $product->photo }}" alt=""
                                    class="w-14 h-14 object-cover rounded-xl border border-gray-200 dark:border-gray-800">
                                <button type="button"
                                    class="absolute -top-1.5 -right-1.5 bg-red-500 hover:bg-red-600 text-white rounded-full p-1 shadow-lg opacity-0 group-hover:opacity-100 transition-opacity"
                                    data-remove-photo>
                                    <i class="ri-delete-bin-line text-[10px]"></i>
                                </button>
                                <input type="hidden" name="remove_photo" value="0" data-remove-photo-input>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 2. Satır: Açıklama (Sol) | Meta ve SKU (Sağ) -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-8 pt-4 border-t border-gray-50 dark:border-gray-800">
                <div class="md:col-span-8">
                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 block">Ürün
                        Açıklaması</label>
                    <textarea name="description" rows="12"
                        class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-black text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                        placeholder="Ürün detaylarını buraya yazın...">{{ $product->description }}</textarea>
                </div>
                <div class="flex flex-col gap-5 md:col-span-4">
                    <div>
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1 block">Meta
                            Başlığı</label>
                        <input type="text" name="meta_title" value="{{ $product->meta_title }}"
                            class="w-full px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-black text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                            placeholder="SEO için başlık">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1 block">Meta
                            Açıklaması</label>
                        <input name="meta_description" rows="4"
                            class="w-full px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-black text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all resize-none"
                            placeholder="SEO için kısa açıklama" value="{{ $product->meta_description }}">
                    </div>
                    <div class="pt-2">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1 block">Ürün Kodu
                            (SKU)</label>
                        <div class="relative">
                            <i class="ri-qr-code-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="text" name="code" value="{{ $product->code }}"
                                class="pl-9 w-full px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-black text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                                placeholder="Boş bırakılırsa otomatik oluşur">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Satır: Finansal Bilgiler ve Stok -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 pt-6 border-t border-gray-50 dark:border-gray-800"
                data-tax-fieldset="base">
                <div>
                    <label
                        class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center justify-between mb-1">
                        <span>Satış Fiyatı</span>
                        <span class="text-[10px] text-blue-500 font-normal normal-case" data-tax-rate-hint></span>
                    </label>
                    <div class="relative">
                        <i class="ri-money-dollar-box-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="number" name="price" step="0.01" min="0" value="{{ $product->price }}"
                            required data-tax-gross
                            class="pl-9 w-full px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-black text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1 block">İndirimli
                        Fiyat</label>
                    <div class="relative">
                        <i class="ri-price-tag-3-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="number" name="discount_price" step="0.01" min="0"
                            value="{{ $product->discount_price }}" data-tax-gross
                            class="pl-9 w-full px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-black text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                            placeholder="Yok">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1 block">Vergi Durumu</label>
                    <select name="tax_behavior"
                        class="w-full px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-black text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                        data-tax-behavior>
                        <option value="0" @selected(($product->tax_behavior ?? 0) === 0)>Mağaza oranını kullan</option>
                        <option value="1" @selected(($product->tax_behavior ?? 0) === 1)>Vergiden muaf</option>
                        <option value="2" @selected(($product->tax_behavior ?? 0) === 2)>Özel oran belirle</option>
                    </select>
                    <div class="mt-2 @if (($product->tax_behavior ?? 0) !== 2) hidden @endif" data-tax-custom-rate>
                        <input type="number" name="tax_rate" step="0.01" min="0"
                            value="{{ $product->tax_rate }}" data-tax-rate-input
                            class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-black text-xs"
                            placeholder="Oran (%)">
                    </div>
                </div>
                <div data-stock-block>
                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1 block">Stok Adedi</label>
                    <div class="flex items-center gap-2">
                        <input type="number" name="stock" min="0" value="{{ $product->stock }}"
                            class="flex-1 px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-black text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                            placeholder="0">
                        <label
                            class="flex items-center gap-2 px-3 py-2.5 bg-gray-50 dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 cursor-pointer hover:bg-gray-100 transition-colors shrink-0">
                            <input type="checkbox" name="stock_type" value="1"
                                @if ($product->stock_type == 1) checked @endif class="toggle">
                            <span class="text-[10px] font-bold text-gray-600 dark:text-gray-400 uppercase">Sınırsız</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- 4. Satır: Varyasyonlar -->
            <div class="pt-6 border-t border-gray-50 dark:border-gray-800">
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <span
                            class="text-sm font-bold text-gray-700 dark:text-gray-300 group-hover:text-blue-600 transition-colors">Varyasyonlu
                            Ürün</span>
                        <input type="checkbox" name="use_variants" value="1" id="use-variants-edit" class="toggle"
                            @if ($hasVariants) checked @endif>
                    </label>
                    <span class="text-[11px] text-gray-400 italic">Açıldığında temel stok alanı gizlenir; taban fiyat
                        zorunludur.</span>
                </div>
                <div id="variants-wrap-edit"
                    class="mt-6 @if (!$hasVariants) hidden @endif animate__animated animate__fadeIn animate__faster">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-gray-800 dark:text-white text-sm">Varyant Satırları</h3>
                        <button type="button"
                            class="px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-xl bg-blue-50 text-blue-600 border border-blue-100 hover:bg-blue-100 transition-all"
                            id="add-variant-edit">Yeni Satır Ekle</button>
                    </div>
                    <div class="space-y-3" id="variants-list-edit">
                        @foreach ($product->variants as $i => $v)
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-start bg-gray-50/30 dark:bg-black/40 p-4 rounded-2xl border border-gray-100 dark:border-gray-800"
                                data-variant-row>
                                <div data-variant-attribute>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase mb-1 block">Nitelik</label>
                                    <select name="variants[{{ $i }}][attribute_id]" class="js-select w-full">
                                        <option value="">— Seçin —</option>
                                        @foreach ($attributes ?? [] as $a)
                                            <option value="{{ $a->id }}" @selected($v->attribute_id === $a->id)>
                                                {{ $a->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div data-variant-term>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase mb-1 block">Terim</label>
                                    <select name="variants[{{ $i }}][term_id]" class="js-select w-full">
                                        <option value="">— Seçin —</option>
                                        @foreach ($terms ?? [] as $t)
                                            <option value="{{ $t->id }}" @selected($v->term_id === $t->id)>
                                                {{ $t->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="space-y-3" data-tax-fieldset>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label
                                                class="text-[10px] font-bold text-gray-400 uppercase mb-1 block">Fiyat</label>
                                            <input type="number" step="0.01"
                                                name="variants[{{ $i }}][price]" value="{{ $v->price }}"
                                                data-tax-gross
                                                class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-black text-sm">
                                        </div>
                                        <div>
                                            <label
                                                class="text-[10px] font-bold text-gray-400 uppercase mb-1 block">İndirim</label>
                                            <input type="number" step="0.01"
                                                name="variants[{{ $i }}][discount_price]"
                                                value="{{ $v->discount_price }}" data-tax-gross
                                                class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-black text-sm">
                                        </div>
                                    </div>
                                    <div>
                                        <select name="variants[{{ $i }}][tax_behavior]"
                                            class="w-full px-2 py-1.5 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-black text-xs"
                                            data-tax-behavior>
                                            <option value="0" @selected(($v->tax_behavior ?? 0) === 0)>Mağaza Vergisi</option>
                                            <option value="1" @selected(($v->tax_behavior ?? 0) === 1)>Vergi Muaf</option>
                                            <option value="2" @selected(($v->tax_behavior ?? 0) === 2)>Özel Vergi</option>
                                        </select>
                                        <div class="mt-1 @if (($v->tax_behavior ?? 0) !== 2) hidden @endif"
                                            data-tax-custom-rate>
                                            <input type="number" step="0.01" min="0"
                                                name="variants[{{ $i }}][tax_rate]"
                                                value="{{ $v->tax_rate }}" data-tax-rate-input
                                                class="w-full px-2 py-1.5 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-black text-xs"
                                                placeholder="%">
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2 pt-5">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <input type="number" min="0"
                                                name="variants[{{ $i }}][stock]" value="{{ $v->stock }}" placeholder="stok"
                                                class="flex-1 px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-black text-sm"
                                                data-variant-stock>
                                            <label
                                                class="flex items-center gap-2 px-2 py-2 bg-white dark:bg-black rounded-xl border border-gray-200 dark:border-gray-800 cursor-pointer shrink-0">
                                                <input type="checkbox" name="variants[{{ $i }}][stock_type]"
                                                    value="1" @if ($v->stock_type == 1) checked @endif
                                                    class="toggle" data-variant-stock-type>
                                                <span class="text-[9px] font-bold text-gray-400 uppercase">Sınırsız</span>
                                            </label>
                                        </div>
                                    </div>
                                    <button type="button"
                                        class="p-2.5 rounded-xl bg-red-50 text-red-500 border border-red-100 hover:bg-red-500 hover:text-white transition-all shrink-0"
                                        data-remove>
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. Satır: Aksiyon Butonları (Kart Dışında) -->
        <div class="flex items-center justify-end gap-3 mt-8 pb-10">
            <a href="{{ route('admin.products.index') }}"
                class="px-6 py-2.5 rounded-xl border border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-400 font-bold text-xs uppercase tracking-widest hover:bg-gray-50 transition-all">İptal</a>
            <button type="submit"
                class="px-10 py-2.5 rounded-xl bg-gray-900 text-white dark:bg-white dark:text-black font-bold text-xs uppercase tracking-widest hover:bg-black transition-all shadow-lg shadow-gray-200 dark:shadow-none">Değişiklikleri
                Kaydet</button>
        </div>
    </form>

    @push('scripts')
        @php
            $termsData = ($terms ?? collect())
                ->map(function ($t) {
                    return [
                        'id' => $t->id,
                        'name' => $t->name,
                        'attribute_id' => $t->attribute_id,
                    ];
                })
                ->values()
                ->toArray();
        @endphp
        <script>
            const TAX_CONFIG = @json($storeTaxConfig);
            const TERMS_DATA = @json($termsData);
            const TAX_BEHAVIOR = {
                INHERIT: 0,
                EXEMPT: 1,
                CUSTOM: 2,
            };

            function buildVariantRowEdit(index) {
                return `
		<div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-start bg-gray-50/30 dark:bg-black/40 p-4 rounded-2xl border border-gray-100 dark:border-gray-800 animate__animated animate__slideInDown animate__faster" data-variant-row>
			<div data-variant-attribute>
				<label class="text-[10px] font-bold text-gray-400 uppercase mb-1 block">Nitelik</label>
				<select name="variants[${index}][attribute_id]" class="js-select w-full">
					<option value="">— Seçin —</option>
					@foreach ($attributes ?? [] as $a)
						<option value="{{ $a->id }}">{{ $a->name }}</option>
					@endforeach
				</select>
			</div>
			<div data-variant-term>
				<label class="text-[10px] font-bold text-gray-400 uppercase mb-1 block">Terim</label>
				<select name="variants[${index}][term_id]" class="js-select w-full">
					<option value="">— Seçin —</option>
				</select>
			</div>
			<div class="space-y-3" data-tax-fieldset>
				<div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase mb-1 block">Fiyat</label>
                        <input type="number" step="0.01" name="variants[${index}][price]" data-tax-gross
                            class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-black text-sm">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase mb-1 block">İndirim</label>
                        <input type="number" step="0.01" name="variants[${index}][discount_price]" data-tax-gross
                            class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-black text-sm">
                    </div>
                </div>
				<div>
					<select name="variants[${index}][tax_behavior]" class="w-full px-2 py-1.5 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-black text-xs" data-tax-behavior>
						<option value="0" selected>Mağaza Vergisi</option>
						<option value="1">Vergi Muaf</option>
						<option value="2">Özel Vergi</option>
					</select>
					<div class="mt-1 hidden" data-tax-custom-rate>
						<input type="number" step="0.01" min="0" name="variants[${index}][tax_rate]"
							data-tax-rate-input class="w-full px-2 py-1.5 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-black text-xs" placeholder="%">
					</div>
				</div>
			</div>
			<div class="flex items-start gap-2 pt-5">
				<div class="flex-1">
					<div class="flex items-center gap-2">
                        <input type="number" min="0" name="variants[${index}][stock]" class="flex-1 px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-black text-sm" data-variant-stock>
                        <label class="flex items-center gap-2 px-2 py-2 bg-white dark:bg-black rounded-xl border border-gray-200 dark:border-gray-800 cursor-pointer shrink-0">
                            <input type="checkbox" name="variants[${index}][stock_type]" value="1" checked class="toggle" data-variant-stock-type>
                            <span class="text-[9px] font-bold text-gray-400 uppercase">Sınırsız</span>
                        </label>
                    </div>
				</div>
				<button type="button" class="p-2.5 rounded-xl bg-red-50 text-red-500 border border-red-100 hover:bg-red-500 hover:text-white transition-all shrink-0" data-remove>
                    <i class="ri-delete-bin-line"></i>
                </button>
			</div>
		</div>
	`;
            }

            function filterTermsForAttributeEdit($row) {
                const $attrSelect = $row.find('[data-variant-attribute] select[name$="[attribute_id]"]');
                const $termSelect = $row.find('[data-variant-term] select[name$="[term_id]"]');
                if (!$attrSelect.length || !$termSelect.length || !$termSelect[0].tomselect) {
                    return;
                }
                const attrId = parseInt($attrSelect.val() || '0', 10);
                const ts = $termSelect[0].tomselect;
                const current = ts.getValue();

                ts.clearOptions();
                ts.addOption({ value: '', text: '— Seçin —' });

                const options = TERMS_DATA.filter(t => !attrId || t.attribute_id === attrId);
                options.forEach(t => {
                    ts.addOption({ value: String(t.id), text: t.name });
                });

                if (current && options.some(t => String(t.id) === String(current))) {
                    ts.setValue(String(current), true);
                } else {
                    ts.setValue('', true);
                }
            }

            function bindVariantAttributeChangeEdit($row) {
                const $attrSelect = $row.find('[data-variant-attribute] select[name$="[attribute_id]"]');
                if (!$attrSelect.length) return;
                $attrSelect.on('change', function () {
                    filterTermsForAttributeEdit($row);
                });
            }

            function initTaxFieldset($fieldset) {
                if (!$fieldset || !$fieldset.length) {
                    return;
                }
                const $behavior = $fieldset.find('[data-tax-behavior]');
                const $rateInput = $fieldset.find('[data-tax-rate-input]');
                const $customWrap = $fieldset.find('[data-tax-custom-rate]');
                const $hint = $fieldset.find('[data-tax-rate-hint]');

                const getBehavior = () => $behavior.length ? parseInt($behavior.val() ?? TAX_BEHAVIOR.INHERIT, 10) :
                    TAX_BEHAVIOR.INHERIT;

                const updateHint = () => {
                    if (!$hint.length) {
                        return;
                    }
                    if (!TAX_CONFIG.enabled) {
                        $hint.text('Vergi kapalı');
                        return;
                    }
                    const behavior = getBehavior();
                    if (behavior === TAX_BEHAVIOR.EXEMPT) {
                        $hint.text('Muaf');
                        return;
                    }
                    if (behavior === TAX_BEHAVIOR.CUSTOM) {
                        if (!$rateInput.length) {
                            $hint.text('Oran girin');
                            return;
                        }
                        const val = parseFloat($rateInput.val());
                        if (!Number.isFinite(val) || val <= 0) {
                            $hint.text('Oran girin');
                            return;
                        }
                        $hint.text(`%${val.toFixed(val % 1 === 0 ? 0 : 2)}`);
                        return;
                    }
                    if (TAX_CONFIG.rate) {
                        const base = TAX_CONFIG.rate;
                        $hint.text(`%${base.toFixed(base % 1 === 0 ? 0 : 2)}`);
                        return;
                    }
                    $hint.text('');
                };

                const toggleCustom = () => {
                    if (!$customWrap.length || !$behavior.length) {
                        return;
                    }
                    $customWrap.toggleClass('hidden', getBehavior() !== TAX_BEHAVIOR.CUSTOM);
                };

                if ($behavior.length) {
                    $behavior.on('change', () => {
                        toggleCustom();
                        updateHint();
                    });
                }

                if ($rateInput.length) {
                    $rateInput.on('input', () => {
                        if (getBehavior() === TAX_BEHAVIOR.CUSTOM) {
                            updateHint();
                        }
                    });
                }

                toggleCustom();
                updateHint();
            }

            function syncVariantVisibilityEdit() {
                const isOn = $('#use-variants-edit').is(':checked');
                $('#variants-wrap-edit').toggleClass('hidden', !isOn);
                const $stockBlock = $('[data-stock-block]');
                $stockBlock.toggleClass('hidden', isOn);
            }
            $('#use-variants-edit').on('change', syncVariantVisibilityEdit);
            syncVariantVisibilityEdit();

            $('#add-variant-edit').on('click', function() {
                const list = $('#variants-list-edit');
                const idx = list.children().length;
                const html = buildVariantRowEdit(idx);
                const $row = $(html);
                list.append($row);
                $row.find('.js-select').each(function() {
                    if (!this.tomselect) {
                        new TomSelect(this, {
                            create: false,
                            copyClassesToDropdown: true,
                            plugins: $(this).attr('multiple') !== undefined ? ['remove_button'] : [],
                        });
                    }
                });
                bindVariantAttributeChangeEdit($row);
                filterTermsForAttributeEdit($row);
                $row.find('[data-variant-stock]').on('input', function() {
                    const hasVal = String($(this).val()).trim() !== '';
                    $(this).closest('.flex-1').find('[data-variant-stock-type]').prop('checked', !hasVal);
                });
                $row.find('[data-variant-stock-type]').on('change', function() {
                    if ($(this).is(':checked')) {
                        $(this).closest('.flex-1').find('[data-variant-stock]').val('');
                    }
                });
                initTaxFieldset($row);
            });

            $(document).on('click', '#variants-list-edit [data-remove]', function() {
                $(this).closest('[data-variant-row]').remove();
            });

            $('#variants-list-edit .js-select').each(function() {
                if (!this.tomselect) {
                    new TomSelect(this, {
                        create: false,
                        copyClassesToDropdown: true
                    });
                }
            });

            $('#variants-list-edit').find('[data-variant-stock]').each(function() {
                $(this).on('input', function() {
                    const hasVal = String($(this).val()).trim() !== '';
                    $(this).closest('.flex-1').find('[data-variant-stock-type]').prop('checked', !hasVal);
                });
                const hasVal = String($(this).val()).trim() !== '';
                $(this).closest('.flex-1').find('[data-variant-stock-type]').prop('checked', !hasVal);
            });

            $('#variants-list-edit').find('[data-variant-stock-type]').each(function() {
                $(this).on('change', function() {
                    if ($(this).is(':checked')) {
                        $(this).closest('.flex-1').find('[data-variant-stock]').val('');
                    }
                });
            });

            // Mevcut satırlar için attribute-change bağla ve terimleri filtrele
            $('#variants-list-edit').find('[data-variant-row]').each(function () {
                const $row = $(this);
                bindVariantAttributeChangeEdit($row);
                // TomSelect init edildikten sonra filtre uygulansın
                setTimeout(function () {
                    filterTermsForAttributeEdit($row);
                }, 0);
            });

            (function initBaseStockToggle() {
                const $stock = $('[name="stock"]');
                const $toggle = $('[name="stock_type"]');

                function syncToggle() {
                    const hasVal = String($stock.val()).trim() !== '';
                    $toggle.prop('checked', !hasVal);
                }
                $stock.on('input', syncToggle);
                syncToggle();
            })();

            $('#variants-list-edit').children().each(function() {
                initTaxFieldset($(this));
            });
            initTaxFieldset($('[data-tax-fieldset="base"]'));

            $('#product-edit').on('submit', function(e) {
                e.preventDefault();
                const form = this;
                const hadVariants = {{ $hasVariants ? 'true' : 'false' }};
                const isVariantsNow = $('#use-variants-edit').is(':checked');
                const proceed = function(resetFlag) {
                    const formData = new FormData(form);
                    if (resetFlag) {
                        formData.append('reset_variants', '1');
                    }
                    $.ajax({
                        url: "{{ route('admin.products.update', $product->id) }}",
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(res) {
                            showSuccess(res?.msg);
                            setTimeout(function() {
                                window.location = "{{ route('admin.products.index') }}";
                            }, 600);
                        },
                        error: function(xhr) {
                            const msg = xhr.responseJSON?.msg || 'Hata';
                            showError(msg);
                        }
                    });
                };

                if (hadVariants && !isVariantsNow) {
                    const $overlay = $(
                        '<div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"></div>');
                    const $box = $(
                        '<div class="max-w-md w-full rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-black p-6 shadow-2xl"></div>'
                    );
                    $box.append('<div class="font-bold text-lg mb-2">Varyasyonları Sil?</div>');
                    $box.append(
                        '<div class="text-sm text-gray-500 mb-6">Bu ürüne ait tüm varyasyonlar silinerek tek ürün olarak güncellenecektir. Emin misiniz?</div>'
                    );
                    const $actions = $('<div class="flex items-center justify-end gap-2"></div>');
                    const $cancel = $(
                        '<button type="button" class="px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-800 text-xs font-bold uppercase tracking-wider">Vazgeç</button>'
                    );
                    const $ok = $(
                        '<button type="button" class="px-6 py-2 rounded-xl bg-red-500 text-white text-xs font-bold uppercase tracking-wider">Evet, Sil</button>'
                    );
                    $actions.append($cancel, $ok);
                    $box.append($actions);
                    $overlay.append($box);
                    $('body').append($overlay);
                    $cancel.on('click', function() {
                        $overlay.remove();
                    });
                    $ok.on('click', function() {
                        $overlay.remove();
                        proceed(true);
                    });
                } else {
                    proceed(false);
                }
            });

            $(document).on('click', '[data-remove-photo]', function() {
                const $container = $(this).closest('.relative');
                $container.fadeOut(300, function() {
                    $(this).remove();
                });
                $('[data-remove-photo-input]').val('1');
                $('[data-file-input]').val('');
                $('[data-file-label]').text('Yeni fotoğraf seçin...');
            });
        </script>
    @endpush
@endsection
