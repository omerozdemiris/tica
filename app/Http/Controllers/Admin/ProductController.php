<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;
use App\Models\Category;
use App\Services\PricingService;
use App\Services\NotificationService;
use App\Services\Logs\AdminLogService;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('categories');

        $filters = [
            'keyword' => trim((string) $request->input('keyword')),
            'category_id' => $request->input('category_id'),
        ];

        if ($filters['keyword'] !== '') {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['keyword'] . '%')
                    ->orWhere('description', 'like', '%' . $filters['keyword'] . '%');
            });
        }

        if (!empty($filters['category_id'])) {
            $query->whereHas('categories', function ($q) use ($filters) {
                $q->where('categories.id', $filters['category_id']);
            });
        }

        $products = $query->latest()->paginate(20)->appends($request->query());

        if ($request->ajax()) {
            return response()->json($products);
        }

        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('admin.pages.products.index', [
            'products' => $products,
            'filters' => $filters,
            'categories' => $categories,
        ]);
    }

    public function byCategory(Request $request, int $id)
    {
        $category = \App\Models\Category::findOrFail($id);
        $products = Product::with('categories')
            ->whereHas('categories', function ($q) use ($id) {
                $q->where('categories.id', $id);
            })
            ->latest()->paginate(20);
        return view('admin.pages.products.category', compact('products', 'category'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $attributes = \App\Models\Attribute::orderBy('name')->get();
        $terms = \App\Models\Term::orderBy('name')->get();
        return view('admin.pages.products.create', compact('categories', 'attributes', 'terms'));
    }

    public function store(Request $request)
    {
        if (!$request->filled('code')) {
            do {
                $code = strtoupper(Str::random(10));
            } while (Product::where('code', $code)->exists());
            $request->merge(['code' => $code]);
        }

        $messages = [
            'title.required' => 'Başlık alanı zorunludur.',
            'price.required_without' => 'Varyasyonlu değilken Fiyat alanı zorunludur.',
            'price.numeric' => 'Fiyat sayısal olmalıdır.',
            'stock.required_without' => 'Varyasyonlu değilken Stok alanı zorunludur.',
            'stock.integer' => 'Stok tam sayı olmalıdır.',
            'variants.*.price.numeric' => 'Varyant fiyatı sayısal olmalıdır.',
            'variants.*.stock.integer' => 'Varyant stoku tam sayı olmalıdır.',
            'tax_rate.required_if' => 'Vergi oranı zorunludur.',
            'tax_rate.numeric' => 'Vergi oranı sayısal olmalıdır.',
            'variants.*.tax_rate.numeric' => 'Varyant vergi oranı sayısal olmalıdır.',
            'category_ids.*.exists' => 'Seçilen kategori bulunamadı.',
        ];
        $attributes = [
            'title' => 'Başlık',
            'description' => 'Açıklama',
            'price' => 'Fiyat',
            'discount_price' => 'İndirimli Fiyat',
            'tax_behavior' => 'Vergi davranışı',
            'tax_rate' => 'Vergi oranı',
            'stock' => 'Stok',
            'stock_type' => 'Stok tipi',
            'meta_title' => 'Meta Başlığı',
            'meta_description' => 'Meta Açıklaması',
            'category_ids' => 'Kategoriler',
            'use_variants' => 'Varyasyonlu ürün',
            'variants.*.attribute_id' => 'Varyant nitelik',
            'variants.*.term_id' => 'Varyant terim',
            'variants.*.price' => 'Varyant fiyatı',
            'variants.*.discount_price' => 'Varyant indirimli fiyatı',
            'variants.*.tax_behavior' => 'Varyant vergi davranışı',
            'variants.*.tax_rate' => 'Varyant vergi oranı',
            'variants.*.stock' => 'Varyant stok',
            'variants.*.stock_type' => 'Varyant stok tipi',
        ];
        $validator = Validator::make($request->all(), [
            'code' => ['nullable', 'string', 'max:255', 'unique:products,code'],
            'title' => ['required', 'string', 'max:255', 'special_characters'],
            'description' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255', 'special_characters'],
            'meta_description' => ['nullable', 'string', 'special_characters'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'tax_behavior' => ['required', 'integer', 'in:0,1,2'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100', 'required_if:tax_behavior,2'],
            'stock' => ['required_without_all:use_variants,stock_type', 'nullable', 'integer', 'min:0'],
            'stock_type' => ['nullable', 'in:0,1'],
            'meta_title' => ['nullable', 'string', 'max:255', 'special_characters'],
            'meta_description' => ['nullable', 'string', 'special_characters'],
            'photo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif,avif', 'max:5120'],
            'category_ids' => ['array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'use_variants' => ['nullable', 'boolean'],
            'variants' => ['array'],
            'variants.*.attribute_id' => ['nullable', 'integer', 'exists:attributes,id'],
            'variants.*.term_id' => ['nullable', 'integer', 'exists:terms,id'],
            'variants.*.price' => ['nullable', 'required_with:variants.*.discount_price', 'numeric'],
            'variants.*.discount_price' => ['nullable', 'numeric', 'min:0', 'lt:variants.*.price'],
            'variants.*.tax_behavior' => ['nullable', 'integer', 'in:0,1,2'],
            'variants.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'variants.*.stock' => ['nullable', 'integer', 'min:0'],
            'variants.*.stock_type' => ['nullable', 'in:0,1'],
        ], $messages, $attributes);

        $validator->after(function ($validator) use ($request) {
            $variants = $request->input('variants', []);
            if (!is_array($variants)) {
                return;
            }
            foreach ($variants as $index => $variant) {
                $behavior = (int) ($variant['tax_behavior'] ?? PricingService::TAX_BEHAVIOR_INHERIT);
                if (
                    $behavior === PricingService::TAX_BEHAVIOR_CUSTOM
                    && (($variant['tax_rate'] ?? null) === null || $variant['tax_rate'] === '')
                ) {
                    $validator->errors()->add("variants.{$index}.tax_rate", 'Varyant vergi oranı zorunludur.');
                }
            }
        });

        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray(), implode(' ', $validator->errors()->all()));
        }
        $data = $validator->validated();

        $uploadDir = public_path('upload/products');
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }

        $photoPath = null;
        if ($request->hasFile('photo_file')) {
            $file = $request->file('photo_file');
            $unique = 'product_' . uniqid('', true) . '.webp';
            $target = $uploadDir . '/' . $unique;

            $moved = false;
            try {
                $imageData = file_get_contents($file->getRealPath());
                $img = imagecreatefromstring($imageData);
                if ($img !== false && function_exists('imagewebp')) {
                    imagewebp($img, $target, 85);
                    imagedestroy($img);
                    $moved = true;
                }
            } catch (\Throwable $e) {
            }
            if (!$moved) {
                $file->move($uploadDir, $unique);
            }

            $photoPath = '/upload/products/' . $unique;
        }

        $productTaxBehavior = $this->normalizeTaxBehavior($data['tax_behavior'] ?? null);
        $productTaxRate = $this->prepareTaxRate($data['tax_rate'] ?? null, $productTaxBehavior);

        $product = Product::create([
            'code' => $data['code'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'] ?? null,
            'discount_price' => $data['discount_price'] ?? null,
            'tax_behavior' => $productTaxBehavior,
            'tax_rate' => $productTaxRate,
            'stock' => $data['stock'] ?? null,
            'stock_type' => (int)($data['stock_type'] ?? 0),
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'photo' => $photoPath,
        ]);

        app(AdminLogService::class)->log('Ürün Oluşturuldu', null, $product->toArray());

        if (!empty($data['category_ids'])) {
            $product->categories()->sync($data['category_ids']);
        }
        if (!empty($data['use_variants']) && !empty($data['variants'])) {
            foreach ($data['variants'] as $row) {
                if (empty($row['attribute_id']) && empty($row['term_id']) && $row['price'] === null && $row['stock'] === null) {
                    continue;
                }
                $variantBehavior = $this->normalizeTaxBehavior($row['tax_behavior'] ?? null);
                $variantRate = $this->prepareTaxRate($row['tax_rate'] ?? null, $variantBehavior);
                $product->variants()->create([
                    'attribute_id' => $row['attribute_id'] ?? null,
                    'term_id' => $row['term_id'] ?? null,
                    'price' => $row['price'] ?? null,
                    'discount_price' => $row['discount_price'] ?? null,
                    'tax_behavior' => $variantBehavior,
                    'tax_rate' => $variantRate,
                    'stock' => $row['stock'] ?? null,
                    'stock_type' => isset($row['stock']) && $row['stock'] !== null && $row['stock'] !== '' ? 0 : (int)($row['stock_type'] ?? 1),
                ]);
            }
        }
        return $this->jsonSuccess('Ürün oluşturuldu', ['id' => $product->id]);
    }

    public function edit(int $id)
    {
        $product = Product::with(['categories', 'variants'])->findOrFail($id);
        $categories = Category::orderBy('name')->get();
        $attributes = \App\Models\Attribute::orderBy('name')->get();
        $terms = \App\Models\Term::orderBy('name')->get();
        return view('admin.pages.products.edit', compact('product', 'categories', 'attributes', 'terms'));
    }

    public function update(Request $request, int $id)
    {
        if (!$request->filled('code')) {
            do {
                $code = strtoupper(Str::random(10));
            } while (Product::where('code', $code)->where('id', '!=', $id)->exists());
            $request->merge(['code' => $code]);
        }

        $product = Product::findOrFail($id);
        $before = $product->toArray();
        $oldProductData = $product->only(['price', 'discount_price', 'stock', 'stock_type']);
        $oldVariants = $product->variants->map(function ($v) {
            return [
                'attribute_id' => $v->attribute_id,
                'term_id' => $v->term_id,
                'price' => $v->price,
                'discount_price' => $v->discount_price,
                'stock' => $v->stock,
                'stock_type' => $v->stock_type,
            ];
        })->toArray();

        $messages = [
            'title.required' => 'Başlık alanı zorunludur.',
            'price.required_without' => 'Varyasyonlu değilken Fiyat alanı zorunludur.',
            'price.numeric' => 'Fiyat sayısal olmalıdır.',
            'stock.required_without' => 'Varyasyonlu değilken Stok alanı zorunludur.',
            'stock.integer' => 'Stok tam sayı olmalıdır.',
            'variants.*.price.numeric' => 'Varyant fiyatı sayısal olmalıdır.',
            'variants.*.stock.integer' => 'Varyant stoku tam sayı olmalıdır.',
            'tax_rate.required_if' => 'Vergi oranı zorunludur.',
            'tax_rate.numeric' => 'Vergi oranı sayısal olmalıdır.',
            'variants.*.tax_rate.numeric' => 'Varyant vergi oranı sayısal olmalıdır.',
            'category_ids.*.exists' => 'Seçilen kategori bulunamadı.',
        ];
        $attributes = [
            'title' => 'Başlık',
            'description' => 'Açıklama',
            'price' => 'Fiyat',
            'discount_price' => 'İndirimli Fiyat',
            'tax_behavior' => 'Vergi davranışı',
            'tax_rate' => 'Vergi oranı',
            'stock' => 'Stok',
            'stock_type' => 'Stok tipi',
            'meta_title' => 'Meta Başlığı',
            'meta_description' => 'Meta Açıklaması',
            'category_ids' => 'Kategoriler',
            'use_variants' => 'Varyasyonlu ürün',
            'variants.*.attribute_id' => 'Varyant nitelik',
            'variants.*.term_id' => 'Varyant terim',
            'variants.*.price' => 'Varyant fiyatı',
            'variants.*.discount_price' => 'Varyant indirimli fiyatı',
            'variants.*.tax_behavior' => 'Varyant vergi davranışı',
            'variants.*.tax_rate' => 'Varyant vergi oranı',
            'variants.*.stock' => 'Varyant stok',
            'variants.*.stock_type' => 'Varyant stok tipi',
        ];
        $validator = Validator::make($request->all(), [
            'code' => ['required', 'string', 'max:255', 'unique:products,code,' . $id],
            'title' => ['required', 'string', 'max:255', 'special_characters'],
            'description' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'tax_behavior' => ['required', 'integer', 'in:0,1,2'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100', 'required_if:tax_behavior,2'],
            'stock' => ['required_without_all:use_variants,stock_type', 'nullable', 'integer', 'min:0'],
            'stock_type' => ['nullable', 'in:0,1'],
            'meta_title' => ['nullable', 'string', 'max:255', 'special_characters'],
            'meta_description' => ['nullable', 'string', 'special_characters'],
            'photo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif,avif', 'max:5120'],
            'remove_photo' => ['nullable', 'boolean'],
            'category_ids' => ['array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'use_variants' => ['nullable', 'boolean'],
            'variants' => ['array'],
            'variants.*.attribute_id' => ['nullable', 'integer', 'exists:attributes,id'],
            'variants.*.term_id' => ['nullable', 'integer', 'exists:terms,id'],
            'variants.*.price' => ['nullable', 'required_with:variants.*.discount_price', 'numeric'],
            'variants.*.discount_price' => ['nullable', 'numeric', 'min:0', 'lt:variants.*.price'],
            'variants.*.tax_behavior' => ['nullable', 'integer', 'in:0,1,2'],
            'variants.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'variants.*.stock' => ['nullable', 'integer', 'min:0'],
            'variants.*.stock_type' => ['nullable', 'in:0,1'],
        ], $messages, $attributes);

        $validator->after(function ($validator) use ($request) {
            $variants = $request->input('variants', []);
            if (!is_array($variants)) {
                return;
            }
            foreach ($variants as $index => $variant) {
                $behavior = (int) ($variant['tax_behavior'] ?? PricingService::TAX_BEHAVIOR_INHERIT);
                if (
                    $behavior === PricingService::TAX_BEHAVIOR_CUSTOM
                    && (($variant['tax_rate'] ?? null) === null || $variant['tax_rate'] === '')
                ) {
                    $validator->errors()->add("variants.{$index}.tax_rate", 'Varyant vergi oranı zorunludur.');
                }
            }
        });
        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray(), implode(' ', $validator->errors()->all()));
        }
        $data = $validator->validated();

        $uploadDir = public_path('upload/products');
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }

        $photoPath = $product->photo;

        if ($request->boolean('remove_photo')) {
            if ($product->photo) {
                $oldPhotoPath = public_path($product->photo);
                if (file_exists($oldPhotoPath)) {
                    @unlink($oldPhotoPath);
                }
            }
            $photoPath = null;
        }

        if ($request->hasFile('photo_file')) {
            if ($product->photo) {
                $oldPhotoPath = public_path($product->photo);
                if (file_exists($oldPhotoPath)) {
                    @unlink($oldPhotoPath);
                }
            }

            $file = $request->file('photo_file');
            $unique = 'product_' . uniqid('', true) . '.webp';
            $target = $uploadDir . '/' . $unique;

            $moved = false;
            try {
                $imageData = file_get_contents($file->getRealPath());
                $img = imagecreatefromstring($imageData);
                if ($img !== false && function_exists('imagewebp')) {
                    imagewebp($img, $target, 85);
                    imagedestroy($img);
                    $moved = true;
                }
            } catch (\Throwable $e) {
            }
            if (!$moved) {
                $file->move($uploadDir, $unique);
            }

            $photoPath = '/upload/products/' . $unique;
        }

        $productTaxBehavior = $this->normalizeTaxBehavior($data['tax_behavior'] ?? null);
        $productTaxRate = $this->prepareTaxRate($data['tax_rate'] ?? null, $productTaxBehavior);

        $product->update([
            'code' => $data['code'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'] ?? null,
            'discount_price' => $data['discount_price'] ?? null,
            'tax_behavior' => $productTaxBehavior,
            'tax_rate' => $productTaxRate,
            'stock' => $data['stock'] ?? null,
            'stock_type' => (int)($data['stock_type'] ?? 0),
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'photo' => $photoPath,
        ]);
        if (isset($data['category_ids'])) {
            $product->categories()->sync($data['category_ids']);
        }
        // Varyasyonlu ürün kullanımı (checkbox) her durumda request üzerinden okunur.
        $useVariants = $request->boolean('use_variants');
        if ($useVariants) {
            // Mevcut tüm varyantları temizleyip formdan gelenlerle baştan oluştur.
            $product->variants()->delete();
            if (!empty($data['variants'])) {
                foreach ($data['variants'] as $row) {
                    if (empty($row['attribute_id']) && empty($row['term_id']) && $row['price'] === null && $row['stock'] === null) {
                        continue;
                    }
                    $variantBehavior = $this->normalizeTaxBehavior($row['tax_behavior'] ?? null);
                    $variantRate = $this->prepareTaxRate($row['tax_rate'] ?? null, $variantBehavior);
                    $product->variants()->create([
                        'attribute_id' => $row['attribute_id'] ?? null,
                        'term_id' => $row['term_id'] ?? null,
                        'price' => $row['price'] ?? null,
                        'discount_price' => $row['discount_price'] ?? null,
                        'tax_behavior' => $variantBehavior,
                        'tax_rate' => $variantRate,
                        'stock' => $row['stock'] ?? null,
                        'stock_type' => isset($row['stock']) && $row['stock'] !== null && $row['stock'] !== '' ? 0 : (int)($row['stock_type'] ?? 1),
                    ]);
                }
            }
        } else {
            // Varyasyonlu ürün kapatılmışsa tüm varyantları temizle.
            $product->variants()->delete();
        }

        // Handle Notifications
        $newProductData = $product->fresh()->only(['price', 'discount_price', 'stock', 'stock_type']);
        NotificationService::handleProductUpdate($product, $oldProductData, $newProductData);

        // Handle Variant Notifications (Simplified: if any variant changed significantly)
        foreach ($product->variants as $variant) {
            $oldVariant = collect($oldVariants)->where('attribute_id', $variant->attribute_id)->where('term_id', $variant->term_id)->first();
            if ($oldVariant) {
                NotificationService::handleProductUpdate($product, $oldVariant, $variant->only(['price', 'discount_price', 'stock', 'stock_type']));
            }
        }

        app(AdminLogService::class)->log('Ürün Güncellendi', $before, $product->fresh()->toArray());

        return $this->jsonSuccess('Ürün güncellendi');
    }

    public function destroy(int $id)
    {
        $product = Product::findOrFail($id);
        $before = $product->toArray();
        $product->delete();

        app(AdminLogService::class)->log('Ürün Silindi', $before, null);

        return $this->jsonSuccess('Ürün silindi');
    }

    protected function normalizeTaxBehavior($value): int
    {
        $behavior = is_numeric($value) ? (int) $value : PricingService::TAX_BEHAVIOR_INHERIT;

        return in_array($behavior, [
            PricingService::TAX_BEHAVIOR_INHERIT,
            PricingService::TAX_BEHAVIOR_EXEMPT,
            PricingService::TAX_BEHAVIOR_CUSTOM,
        ], true) ? $behavior : PricingService::TAX_BEHAVIOR_INHERIT;
    }

    protected function prepareTaxRate($value, int $behavior): ?float
    {
        if ($behavior !== PricingService::TAX_BEHAVIOR_CUSTOM) {
            return null;
        }

        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }
}
