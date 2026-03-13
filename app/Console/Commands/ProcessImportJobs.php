<?php

namespace App\Console\Commands;

use App\Models\ImportJob;
use App\Services\ProductBulkExcelService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class ProcessImportJobs extends Command
{
    protected $signature = 'imports:process 
                            {--chunk=200 : Her chunk\'ta işlenecek satır sayısı}
                            {--limit=1 : Tek seferde işlenecek maksimum job sayısı}';

    protected $description = 'Bekleyen Excel import job\'larını işler';

    public function handle(ProductBulkExcelService $service): int
    {
        $this->logCron('Command başlatıldı');

        $limit = (int) $this->option('limit');
        $chunkSize = (int) $this->option('chunk');

        $jobs = ImportJob::where('status', ImportJob::STATUS_PENDING)
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();

        if ($jobs->isEmpty()) {
            $this->logCron('Bekleyen job yok');
            $this->info('İşlenecek bekleyen job bulunamadı.');
            return self::SUCCESS;
        }

        $this->logCron("Bulunan job sayısı: {$jobs->count()}");

        foreach ($jobs as $job) {
            $this->logCron("Job #{$job->id} işleniyor");
            $this->info("Job #{$job->id} işleniyor: {$job->file_path}");

            try {
                $result = $service->processJob($job, $chunkSize);

                if ($result['success']) {
                    $this->logCron("Job #{$job->id} tamamlandı. Güncellenen: {$result['updated']}");
                    $this->info("Job #{$job->id} başarıyla tamamlandı. Güncellenen: {$result['updated']}");
                } else {
                    $this->logCron("Job #{$job->id} başarısız: {$result['message']}");
                    $this->error("Job #{$job->id} başarısız: {$result['message']}");
                }

                if (!empty($result['errors'])) {
                    foreach (array_slice($result['errors'], 0, 10) as $error) {
                        $this->warn("  - {$error}");
                    }
                    if (count($result['errors']) > 10) {
                        $this->warn("  ... ve " . (count($result['errors']) - 10) . " hata daha.");
                    }
                }
            } catch (\Throwable $e) {
                $this->logCron("Job #{$job->id} HATA: {$e->getMessage()}");
                Log::error("Import job #{$job->id} işlenirken hata", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                $job->update([
                    'status' => ImportJob::STATUS_FAILED,
                    'error' => $e->getMessage(),
                ]);

                $this->error("Job #{$job->id} exception: {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }

    /**
     * Cron log dosyasına yaz (FTP ile kontrol için)
     */
    protected function logCron(string $message): void
    {
        $logFile = storage_path('logs/cron-import.log');
        $timestamp = now()->format('Y-m-d H:i:s');
        $line = "[{$timestamp}] {$message}" . PHP_EOL;
        
        File::append($logFile, $line);
    }
}