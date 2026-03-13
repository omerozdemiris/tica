<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductAttributeTerm;
use App\Models\ProductBackup;
use App\Models\ImportJob;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;

class ProductBulkExcelService
{
    public const MAX_FILE_SIZE = 52428800; // 50MB
    public const DEFAULT_CHUNK_SIZE = 100;

    protected array $baseHeaders = [
        'ID',
        'Ürün Adı',
        'Fiyat',
        'Stok',
        'Stok Tipi',
        'Aktif mi',
        'Varyantlı mı',
        'Var. Sayısı',
        'Var. Stk. Toplamı',
    ];

    protected array $allowedHeaders = [];

    protected array $headerMap = [
        'ID' => 'id',
        'Ürün Adı' => 'title',
        'Fiyat' => 'price',
        'Stok' => 'stock',
        'Stok Tipi' => 'stock_type',
        'Aktif mi' => 'is_active',
        'Varyantlı mı' => 'has_variants',
        'Var. Sayısı' => 'variants_count',
        'Var. Stk. Toplamı' => 'variants_stock_sum',
    ];

    protected int $maxVariants = 0;

    public function __construct()
    {
        $this->maxVariants = (int) (Product::withCount('variants')->get()->max('variants_count') ?? 0);

        $this->allowedHeaders = $this->baseHeaders;

        if ($this->maxVariants > 0) {
            for ($i = 1; $i <= $this->maxVariants; $i++) {
                $this->allowedHeaders[] = "Var. {$i} Adı";
                $this->allowedHeaders[] = "Var. {$i} Değeri";
                $this->allowedHeaders[] = "Var. {$i} Terimi";
                $this->allowedHeaders[] = "Var. {$i} Stoğu";
                $this->allowedHeaders[] = "Var. {$i} Fiyatı";
                $this->allowedHeaders[] = "Var. {$i} Attr ID";
                $this->allowedHeaders[] = "Var. {$i} Term ID";
            }
        }
    }

    public function downloadTemplate()
    {
        $products = Product::withCount('variants')
            ->with(['variants.attribute', 'variants.term'])
            ->orderBy('id')
            ->get();

        $rows = collect();

        foreach ($products as $product) {
            $variantsStock = (int) ($product->variants->sum('stock') ?? 0);

            $base = [
                'ID' => $product->id,
                'Ürün Adı' => $product->title,
                'Fiyat' => $product->price,
                'Stok' => $product->stock,
                'Stok Tipi' => $product->stock_type,
                'Aktif mi' => $product->is_active ? 1 : 0,
                'Varyantlı mı' => $product->variants_count > 0 ? 'Evet' : 'Hayır',
                'Var. Sayısı' => $product->variants_count,
                'Var. Stk. Toplamı' => $variantsStock,
            ];

            $rows->push($base + $this->flattenVariants($product->variants));
        }

        $export = new class($rows, $this->allowedHeaders) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithEvents {
            public function __construct(private Collection $rows, private array $headers) {}

            public function collection()
            {
                return $this->rows;
            }

            public function headings(): array
            {
                return $this->headers;
            }

            public function registerEvents(): array
            {
                return [
                    \Maatwebsite\Excel\Events\AfterSheet::class => function ($event) {
                        foreach ($this->headers as $index => $header) {
                            $columnIndex = $index + 1;
                            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex);
                            $width = max(10, mb_strlen($header, 'UTF-8') + 4);
                            $event->sheet->getDelegate()->getColumnDimension($columnLetter)->setWidth($width);
                        }
                    },
                ];
            }
        };

        $filename = 'urunler-' . now()->format('d.m.Y') . '.xlsx';

        return Excel::download($export, $filename);
    }

    /**
     * Sadece dosyayı kaydet ve ImportJob oluştur (validating durumunda).
     * Doğrulama ayrı validateJob() ile AJAX üzerinden yapılır.
     */
    public function storeFileAndCreateJob(UploadedFile $file, ?string $label = null): array
    {
        $originalSize = $file->getSize();

        if ($originalSize > self::MAX_FILE_SIZE) {
            return [
                'success' => false,
                'message' => 'Dosya boyutu 50MB limitini aşıyor.',
                'job_id' => null,
            ];
        }

        $path = $this->storeUploadedFile($file);

        $job = ImportJob::create([
            'file_path' => $path,
            'status' => ImportJob::STATUS_VALIDATING,
            'total_rows' => 0,
            'processed_rows' => 0,
            'is_locked' => false,
            'error' => null,
        ]);

        return [
            'success' => true,
            'message' => 'Dosya yüklendi, doğrulanıyor...',
            'job_id' => $job->id,
        ];
    }

    /**
     * AJAX ile çağrılır: Excel dosyasını doğrula (başlık, satır sayısı).
     * Başarılıysa job'ı pending yapar, hata varsa failed.
     */
    public function validateJob(ImportJob $job, ?string $label = null): array
    {
        if ($job->status !== ImportJob::STATUS_VALIDATING) {
            return [
                'success' => false,
                'message' => 'Job zaten doğrulanmış veya işleniyor.',
                'validated' => false,
            ];
        }

        $path = $this->resolveFilePath($job->file_path);
        $fullPath = file_exists($path) ? $path : public_path($job->file_path);

        if (!file_exists($fullPath)) {
            $job->markAsFailed('Dosya bulunamadı: ' . $job->file_path);
            return [
                'success' => false,
                'message' => 'Dosya bulunamadı.',
                'validated' => false,
            ];
        }

        HeadingRowFormatter::default('none');

        try {
            $headings = (new HeadingRowImport)->toArray($fullPath)[0][0] ?? [];
        } catch (\Throwable $e) {
            $job->markAsFailed('Excel dosyası okunamadı: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Excel dosyası okunamadı: ' . $e->getMessage(),
                'validated' => false,
            ];
        }

        if ($headings !== $this->allowedHeaders) {
            $job->markAsFailed('Excel sütun düzeni beklenen şablonla eşleşmiyor.');
            return [
                'success' => false,
                'message' => 'Excel sütun düzeni beklenen şablonla eşleşmiyor.',
                'validated' => false,
            ];
        }

        $rows = Excel::toCollection(null, $fullPath)->first();
        if (!$rows || $rows->isEmpty()) {
            $job->markAsFailed('Excel dosyasında veri bulunamadı.');
            return [
                'success' => false,
                'message' => 'Excel dosyasında veri bulunamadı.',
                'validated' => false,
            ];
        }

        $totalRows = $rows->count() - 1;

        $expectedCount = Product::count();
        $actualCount = $rows->skip(1)->filter(function ($row) {
            $id = $row[0] ?? null;
            return $id !== null && $id !== '';
        })->count();

        if ($actualCount !== $expectedCount) {
            $job->markAsFailed('Satır sayısı şablon ile eşleşmiyor. Ürün satırı ekleyemez veya silemezsiniz.');
            return [
                'success' => false,
                'message' => 'Satır sayısı şablon ile eşleşmiyor. Ürün satırı ekleyemez veya silemezsiniz.',
                'validated' => false,
            ];
        }

        $job->update([
            'status' => ImportJob::STATUS_PENDING,
            'total_rows' => $totalRows,
        ]);

        if ($label) {
            $this->createBackupRecord($job->file_path, $label);
        }

        return [
            'success' => true,
            'message' => 'Doğrulama tamamlandı, işlem başlatılıyor.',
            'validated' => true,
            'total_rows' => $totalRows,
        ];
    }

    /**
     * Eski createImportJob - geriye dönük uyumluluk (form POST ile kullanım)
     */
    public function createImportJob(UploadedFile $file, ?string $label = null): array
    {
        $storeResult = $this->storeFileAndCreateJob($file, $label);
        if (!$storeResult['success']) {
            return $storeResult;
        }

        $job = ImportJob::find($storeResult['job_id']);
        $validateResult = $this->validateJob($job, $label);

        if (!$validateResult['validated']) {
            return [
                'success' => false,
                'message' => $validateResult['message'],
                'job_id' => null,
            ];
        }

        return [
            'success' => true,
            'message' => 'Import job oluşturuldu.',
            'job_id' => $job->id,
            'total_rows' => $validateResult['total_rows'],
        ];
    }

    /**
     * Frontend polling ile çağrılır: Sonraki chunk'ı işle
     * Her çağrıda $chunkSize kadar satır işler
     */
    public function processNextChunk(ImportJob $job, int $chunkSize = self::DEFAULT_CHUNK_SIZE): array
    {
        if ($job->status === ImportJob::STATUS_CANCELLED) {
            $job->releaseLock();
            return [
                'success' => false,
                'finished' => true,
                'processed_rows' => $job->processed_rows,
                'progress' => $job->progress(),
                'chunk_updated' => 0,
                'message' => 'Job iptal edilmiş.',
                'errors' => [],
            ];
        }

        if ($job->status === ImportJob::STATUS_VALIDATING) {
            return [
                'success' => true,
                'finished' => false,
                'processed_rows' => 0,
                'progress' => 0,
                'chunk_updated' => 0,
                'status' => ImportJob::STATUS_VALIDATING,
                'message' => 'Dosya doğrulanıyor, lütfen bekleyin...',
                'errors' => [],
            ];
        }

        if ($job->status === ImportJob::STATUS_PENDING) {
            $job->markAsProcessing();
        }

        HeadingRowFormatter::default('none');

        $path = $this->resolveFilePath($job->file_path);

        if (!file_exists($path)) {
            $job->markAsFailed('Dosya bulunamadı: ' . $job->file_path);
            return [
                'success' => false,
                'finished' => true,
                'processed_rows' => $job->processed_rows,
                'progress' => $job->progress(),
                'chunk_updated' => 0,
                'message' => 'Dosya bulunamadı.',
                'errors' => [],
            ];
        }

        $rows = Excel::toCollection(null, $path)->first();
        if (!$rows || $rows->isEmpty()) {
            $job->markAsFailed('Excel dosyasında veri bulunamadı.');
            return [
                'success' => false,
                'finished' => true,
                'processed_rows' => $job->processed_rows,
                'progress' => $job->progress(),
                'chunk_updated' => 0,
                'message' => 'Excel dosyasında veri bulunamadı.',
                'errors' => [],
            ];
        }

        $rows = $rows->values();
        $rows->shift(); // Başlık satırını kaldır

        $startIndex = $job->processed_rows;
        $endIndex = min($startIndex + $chunkSize, $rows->count());

        if ($startIndex >= $rows->count()) {
            $job->markAsCompleted();
            return [
                'success' => true,
                'finished' => true,
                'processed_rows' => $job->processed_rows,
                'progress' => 100,
                'chunk_updated' => 0,
                'message' => 'Tüm satırlar işlendi.',
                'errors' => [],
            ];
        }

        $chunk = $rows->slice($startIndex, $chunkSize)->values();
        $chunkResult = $this->processChunkRows($chunk, $startIndex);

        $newProcessedRows = $startIndex + $chunk->count();
        $isFinished = $newProcessedRows >= $job->total_rows;

        if ($isFinished) {
            $errorSummary = empty($chunkResult['errors']) ? null : implode("\n", array_slice($chunkResult['errors'], 0, 50));
            $job->update([
                'processed_rows' => $newProcessedRows,
                'status' => ImportJob::STATUS_COMPLETED,
                'error' => $errorSummary,
                'is_locked' => false,
                'locked_at' => null,
            ]);
        } else {
            $job->update([
                'processed_rows' => $newProcessedRows,
                'is_locked' => false,
                'locked_at' => null,
            ]);
        }

        $progress = $job->total_rows > 0 
            ? (int) round(($newProcessedRows / $job->total_rows) * 100) 
            : 0;

        return [
            'success' => true,
            'finished' => $isFinished,
            'processed_rows' => $newProcessedRows,
            'progress' => $progress,
            'chunk_updated' => $chunkResult['updated'],
            'message' => $isFinished ? 'Import tamamlandı.' : 'Chunk işlendi.',
            'errors' => $chunkResult['errors'],
        ];
    }

    /**
     * Tek bir chunk'ı işle (kısa transaction)
     */
    protected function processChunkRows(Collection $chunk, int $startIndex): array
    {
        $updated = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($chunk as $localIndex => $row) {
                $globalIndex = $startIndex + $localIndex;
                $row = $this->normalizeRow($row);

                if (!$row['id']) {
                    continue;
                }

                /** @var Product|null $product */
                $product = Product::find($row['id']);

                if (!$product) {
                    $errors[] = "Satır " . ($globalIndex + 2) . ": Ürün bulunamadı (ID: {$row['id']}).";
                    continue;
                }

                $validationError = $this->validateRow($row);
                if ($validationError) {
                    $errors[] = "Satır " . ($globalIndex + 2) . ": " . $validationError;
                    continue;
                }

                $snapshot = $this->buildSnapshot($product);
                $immutableError = $this->checkImmutableFields($row, $snapshot);
                if ($immutableError) {
                    $errors[] = "Satır " . ($globalIndex + 2) . ": " . $immutableError;
                    continue;
                }

                $product->title = $row['title'] ?? $product->title;
                $product->price = $row['price'];
                $product->stock = $row['stock'];
                $product->stock_type = $row['stock_type'];
                $product->is_active = $row['is_active'];
                $product->save();

                $this->updateVariants($product, $row['variants'] ?? [], $errors, $globalIndex);

                $updated++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Chunk işlenirken hata", [
                'start_index' => $startIndex,
                'error' => $e->getMessage(),
            ]);
            $errors[] = "Chunk hatası (satır ~{$startIndex}): " . $e->getMessage();
        }

        return [
            'updated' => $updated,
            'errors' => $errors,
        ];
    }

    /**
     * Dosya yolunu çözümle (relative veya absolute)
     */
    protected function resolveFilePath(string $path): string
    {
        if (file_exists($path)) {
            return $path;
        }

        $publicPath = public_path($path);
        if (file_exists($publicPath)) {
            return $publicPath;
        }

        return $path;
    }

    public function restoreFromBackup(ProductBackup $backup): array
    {
        $fullPath = public_path($backup->file_name);

        if (!file_exists($fullPath)) {
            return [
                'success' => false,
                'message' => 'Yedek dosyası bulunamadı.',
                'updated' => 0,
                'errors' => [],
            ];
        }

        $size = filesize($fullPath) ?: 0;

        $result = $this->processImportSync($fullPath, $size);

        if ($result['success']) {
            ProductBackup::where('id', '!=', $backup->id)->update(['is_default' => false]);
            $backup->is_default = true;
            $backup->save();
        }

        return $result;
    }

    /**
     * Senkron import (restore için kullanılır)
     */
    protected function processImportSync(string $path, int $size): array
    {
        HeadingRowFormatter::default('none');

        if (!file_exists($path)) {
            $publicPath = public_path($path);
            if (file_exists($publicPath)) {
                $path = $publicPath;
            }
        }

        if (!file_exists($path)) {
            return [
                'success' => false,
                'message' => 'İçeri aktarma sırasında dosya bulunamadı: ' . $path,
                'updated' => 0,
                'errors' => [],
            ];
        }

        if ($size > self::MAX_FILE_SIZE) {
            return [
                'success' => false,
                'message' => 'Dosya boyutu 50MB limitini aşıyor.',
                'updated' => 0,
                'errors' => [],
            ];
        }

        $headings = (new HeadingRowImport)->toArray($path)[0][0] ?? [];

        if ($headings !== $this->allowedHeaders) {
            return [
                'success' => false,
                'message' => 'Excel sütun düzeni beklenen şablonla eşleşmiyor.',
                'updated' => 0,
                'errors' => [],
            ];
        }

        $rows = Excel::toCollection(null, $path)->first();
        if (!$rows || $rows->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Excel dosyasında veri bulunamadı.',
                'updated' => 0,
                'errors' => [],
            ];
        }

        $rows = $rows->values();
        $rows->shift();

        $expectedCount = Product::count();
        $actualCount = $rows->filter(function ($row) {
            $id = $row[0] ?? null;
            return $id !== null && $id !== '';
        })->count();

        if ($actualCount !== $expectedCount) {
            return [
                'success' => false,
                'message' => 'Satır sayısı şablon ile eşleşmiyor. Ürün satırı ekleyemez veya silemezsiniz.',
                'updated' => 0,
                'errors' => [],
            ];
        }

        $updated = 0;
        $errors = [];

        $chunks = $rows->chunk(100);

        foreach ($chunks as $chunkIndex => $chunk) {
            $chunkResult = $this->processChunkRows($chunk, $chunkIndex * 100);
            $updated += $chunkResult['updated'];
            $errors = array_merge($errors, $chunkResult['errors']);
        }

        return [
            'success' => empty($errors),
            'message' => empty($errors) ? 'Ürünler başarıyla güncellendi.' : 'Bazı satırlarda hatalar var.',
            'updated' => $updated,
            'errors' => $errors,
        ];
    }

    protected function storeUploadedFile(UploadedFile $file): string
    {
        $relativeDir = 'upload/products/excels';
        $absoluteDir = public_path($relativeDir);

        if (!is_dir($absoluteDir)) {
            mkdir($absoluteDir, 0755, true);
        }

        $filename = 'urunler-' . now()->format('d.m.Y-His') . '-' . uniqid() . '.' . $file->getClientOriginalExtension();

        $file->move($absoluteDir, $filename);

        return $relativeDir . '/' . $filename;
    }

    protected function createBackupRecord(string $filePath, ?string $label = null): void
    {
        ProductBackup::where('is_default', true)->update(['is_default' => false]);

        ProductBackup::create([
            'file_name' => $filePath,
            'label' => $label,
            'is_default' => true,
        ]);
    }

    protected function normalizeRow($row): array
    {
        $data = [];
        $row = $row->toArray();

        foreach ($this->baseHeaders as $i => $header) {
            $field = $this->headerMap[$header] ?? null;
            if ($field) {
                $data[$field] = $row[$i] ?? null;
            }
        }

        $data['id'] = $data['id'] !== null ? (int) $data['id'] : null;
        $data['price'] = $data['price'] !== null && $data['price'] !== '' ? (float) $data['price'] : null;
        $data['stock'] = $data['stock'] !== null && $data['stock'] !== '' ? (int) $data['stock'] : null;
        $data['stock_type'] = (int) ($data['stock_type'] ?? 0);
        $data['is_active'] = (int) ($data['is_active'] ?? 1) ? 1 : 0;

        $hasVariantsRaw = $data['has_variants'] ?? 0;
        if (is_string($hasVariantsRaw)) {
            $normalized = mb_strtolower(trim($hasVariantsRaw), 'UTF-8');
            $data['has_variants'] = in_array($normalized, ['evet', '1', 'true'], true) ? 1 : 0;
        } else {
            $data['has_variants'] = (int) $hasVariantsRaw;
        }
        $data['variants_count'] = (int) ($data['variants_count'] ?? 0);
        $data['variants_stock_sum'] = (int) ($data['variants_stock_sum'] ?? 0);

        $variants = [];
        $offset = count($this->baseHeaders);

        $chunkSize = 7;

        for ($i = 0; $i < $this->maxVariants; $i++) {
            $idx = $offset + ($i * $chunkSize);
            $name = $row[$idx] ?? null;
            $value = $row[$idx + 1] ?? null;
            $term = $row[$idx + 2] ?? null;
            $stock = $row[$idx + 3] ?? null;
            $price = $row[$idx + 4] ?? null;
            $attrId = $row[$idx + 5] ?? null;
            $termId = $row[$idx + 6] ?? null;

            if ($name === null && $value === null && $term === null && $stock === null && $price === null && $attrId === null && $termId === null) {
                continue;
            }

            $variants[] = [
                'name' => $name,
                'value' => $value,
                'term' => $term,
                'stock' => $stock !== null && $stock !== '' ? (int) $stock : null,
                'price' => $price !== null && $price !== '' ? (float) $price : null,
                'attribute_id' => $attrId !== null && $attrId !== '' ? (int) $attrId : null,
                'term_id' => $termId !== null && $termId !== '' ? (int) $termId : null,
            ];
        }

        $data['variants'] = $variants;

        return $data;
    }

    protected function validateRow(array $row): ?string
    {
        if ($row['price'] !== null && $row['price'] < 0) {
            return 'Fiyat negatif olamaz.';
        }
        if ($row['stock'] !== null && $row['stock'] < 0) {
            return 'Stok negatif olamaz.';
        }
        if (!in_array($row['stock_type'], [0, 1, 2], true)) {
            return 'Geçersiz stok tipi (0 veya 1 olmalı).';
        }

        return null;
    }

    protected function flattenVariants(Collection $variants): array
    {
        $result = [];

        if ($this->maxVariants <= 0) {
            return $result;
        }

        $variants = $variants->values();

        for ($i = 0; $i < $this->maxVariants; $i++) {
            $variant = $variants[$i] ?? null;
            $index = $i + 1;

            $result["Var. {$index} Adı"] = $variant?->attribute?->name;
            $result["Var. {$index} Değeri"] = $variant?->term?->name;
            $result["Var. {$index} Terimi"] = $variant?->term?->value;
            $result["Var. {$index} Stoğu"] = $variant?->stock;
            $result["Var. {$index} Fiyatı"] = $variant?->price;
            $result["Var. {$index} Attr ID"] = $variant?->attribute_id;
            $result["Var. {$index} Term ID"] = $variant?->term_id;
        }

        return $result;
    }

    protected function updateVariants(Product $product, array $variants, array &$errors, int $index): void
    {
        if (empty($variants)) {
            return;
        }

        $existing = $product->variants()->get()->keyBy(function ($v) {
            return $v->attribute_id . '-' . $v->term_id;
        });

        foreach ($variants as $i => $data) {
            $attrId = $data['attribute_id'] ?? null;
            $termId = $data['term_id'] ?? null;

            if ($attrId === null || $termId === null) {
                continue;
            }

            $key = $attrId . '-' . $termId;
            $variant = $existing[$key] ?? null;

            if (!$variant) {
                $errors[] = "Satır " . ($index + 2) . ": Varyasyon " . ($i + 1) . " bulunamadı (Attr ID / Term ID eşleşmedi).";
                continue;
            }

            if (array_key_exists('stock', $data) && $data['stock'] !== null) {
                $variant->stock = $data['stock'];
            }

            if (array_key_exists('price', $data) && $data['price'] !== null) {
                $variant->price = $data['price'];
            }

            $variant->save();
        }
    }

    protected function buildSnapshot(Product $product): array
    {
        $product->loadMissing(['variants.attribute', 'variants.term']);

        $variants = $product->variants->values()->map(function ($variant) {
            return [
                'name' => $variant->attribute?->name,
                'value' => $variant->term?->name,
                'term' => $variant->term?->value,
                'stock' => $variant->stock,
                'price' => $variant->price,
                'attribute_id' => $variant->attribute_id,
                'term_id' => $variant->term_id,
            ];
        })->toArray();

        return [
            'id' => $product->id,
            'has_variants' => $product->variants->count() > 0 ? 1 : 0,
            'variants_count' => $product->variants->count(),
            'variants_stock_sum' => (int) $product->variants->sum('stock'),
            'is_active' => $product->is_active ? 1 : 0,
            'stock_type' => (int) $product->stock_type,
            'variants' => $variants,
        ];
    }

    protected function checkImmutableFields(array $row, array $snapshot): ?string
    {
        if ((int) $row['id'] !== (int) $snapshot['id']) {
            return 'ID sütunu değiştirilemez.';
        }

        $immutableScalars = [
            'has_variants' => 'Varyantlı mı',
            'variants_count' => 'Var. Sayısı',
            'variants_stock_sum' => 'Var. Stk. Toplamı',
            'is_active' => 'Aktif mi',
            'stock_type' => 'Stok Tipi',
        ];

        $changedFields = [];

        foreach ($immutableScalars as $key => $label) {
            if (array_key_exists($key, $row) && (string) $row[$key] !== (string) $snapshot[$key]) {
                $changedFields[] = $label;
            }
        }

        $rowVariants = $row['variants'] ?? [];
        $snapVariants = $snapshot['variants'] ?? [];

        if (count($rowVariants) > 0) {
            if (count($rowVariants) !== count($snapVariants)) {
                $changedFields[] = 'Varyasyonlar (sayısı)';
            } else {
                foreach ($rowVariants as $i => $v) {
                    $s = $snapVariants[$i] ?? [];
                    foreach (
                        [
                            'name' => 'Adı',
                            'value' => 'Değeri',
                            'term' => 'Terimi',
                            'attribute_id' => 'Attr ID',
                            'term_id' => 'Term ID',
                        ] as $key => $label
                    ) {
                        $rv = $v[$key] ?? null;
                        $sv = $s[$key] ?? null;
                        if ((string) $rv !== (string) $sv) {
                            $changedFields[] = "Varyasyon " . ($i + 1) . " {$label}";
                            break;
                        }
                    }
                }
            }
        }

        if (!empty($changedFields)) {
            $changedFields = array_unique($changedFields);
            return 'Aşağıdaki sütunlarda değişiklik yapılamaz: ' . implode(', ', $changedFields) . '. Lütfen sadece izin verilen alanları (Ürün Adı, Fiyat, Stok) güncelleyip tekrar deneyin.';
        }

        return null;
    }
}