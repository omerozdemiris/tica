<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Controller;
use App\Services\ProductBulkExcelService;
use App\Models\ProductBackup;
use App\Models\ImportJob;
use App\Services\Logs\AdminLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ProductBulkExcelController extends Controller
{
    public function __construct(private ProductBulkExcelService $service)
    {
        parent::__construct();
    }

    public function index()
    {
        $backups = ProductBackup::orderByDesc('created_at')->get();

        $activeJob = ImportJob::whereIn('status', [
            ImportJob::STATUS_VALIDATING,
            ImportJob::STATUS_PENDING,
            ImportJob::STATUS_PROCESSING
        ])->first();

        return view('admin.pages.products.bulk_excel', [
            'backups' => $backups,
            'activeJob' => $activeJob,
        ]);
    }

    public function download()
    {
        return $this->service->downloadTemplate();
    }

    /**
     * AJAX upload: Sadece dosyayı kaydet, job oluştur (validating), JSON dön.
     * Doğrulama frontend'den validateImport ile yapılır.
     */
    public function upload(Request $request)
    {
        $activeJob = ImportJob::whereIn('status', [
            ImportJob::STATUS_VALIDATING,
            ImportJob::STATUS_PENDING,
            ImportJob::STATUS_PROCESSING
        ])->exists();

        if ($activeJob) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Devam eden bir import işlemi var. Lütfen bekleyin.',
                ], 422);
            }
            return back()->withErrors([
                'msg' => 'Devam eden bir import işlemi var. Lütfen bekleyin.'
            ]);
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:' . (ProductBulkExcelService::MAX_FILE_SIZE / 1024)],
            'label' => ['nullable', 'string', 'max:100'],
        ]);

        $result = $this->service->storeFileAndCreateJob(
            $request->file('file'),
            $request->string('label')->toString() ?: null
        );

        if (!$result['success']) {
            app(AdminLogService::class)->log(
                'Toplu Excel Yükleme Başarısız',
                null,
                ['error' => $result['message'], 'label' => $request->label]
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 422);
            }
            return back()
                ->withErrors(['msg' => $result['message']])
                ->withInput();
        }

        app(AdminLogService::class)->log(
            'Toplu Excel Yükleme Başlatıldı',
            null,
            ['job_id' => $result['job_id'], 'label' => $request->label]
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'job_id' => $result['job_id'],
            ]);
        }

        return back()->with('success', 'Excel dosyası yüklendi. İşlem başlatılıyor...');
    }

    /**
     * AJAX: Job doğrulaması - başlık ve satır sayısı kontrolü
     */
    public function validateImport(Request $request)
    {
        $jobId = $request->input('job_id');
        $job = $jobId ? ImportJob::find($jobId) : null;

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job bulunamadı.',
                'validated' => false,
            ], 404);
        }

        $result = $this->service->validateJob(
            $job,
            $request->input('label')
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'validated' => false,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'validated' => true,
            'job_id' => $job->id,
            'total_rows' => $result['total_rows'],
        ]);
    }

    /**
     * AJAX: Chunk bazlı işleme - Her istekte 100 satır işle
     */
    public function processChunk(ImportJob $job)
    {
        if ($job->isFinished()) {
            return response()->json([
                'success' => true,
                'finished' => true,
                'status' => $job->status,
                'processed_rows' => $job->processed_rows,
                'total_rows' => $job->total_rows,
                'progress' => $job->progress(),
                'error' => $job->error,
                'message' => $job->status === ImportJob::STATUS_COMPLETED 
                    ? 'Import tamamlandı.' 
                    : 'Import ' . $job->status,
            ]);
        }

        if (!$job->acquireLock()) {
            return response()->json([
                'success' => true,
                'locked' => true,
                'finished' => false,
                'status' => $job->status,
                'processed_rows' => $job->processed_rows,
                'total_rows' => $job->total_rows,
                'progress' => $job->progress(),
                'message' => 'İşlem devam ediyor, lütfen bekleyin...',
            ]);
        }

        try {
            $result = $this->service->processNextChunk($job, 100);

            return response()->json([
                'success' => $result['success'],
                'finished' => $result['finished'],
                'locked' => false,
                'status' => $job->fresh()->status,
                'processed_rows' => $result['processed_rows'],
                'total_rows' => $job->total_rows,
                'progress' => $result['progress'],
                'chunk_updated' => $result['chunk_updated'],
                'errors' => $result['errors'] ?? [],
                'message' => $result['message'],
            ]);
        } catch (\Throwable $e) {
            $job->markAsFailed($e->getMessage());

            app(AdminLogService::class)->log(
                'Toplu Excel İşleme Hatası',
                null,
                ['job_id' => $job->id, 'error' => $e->getMessage()]
            );

            return response()->json([
                'success' => false,
                'finished' => true,
                'locked' => false,
                'status' => ImportJob::STATUS_FAILED,
                'processed_rows' => $job->processed_rows,
                'total_rows' => $job->total_rows,
                'progress' => $job->progress(),
                'error' => $e->getMessage(),
                'message' => 'İşlem sırasında hata oluştu.',
            ], 500);
        }
    }

    /**
     * AJAX: Import job durumunu kontrol et
     */
    public function checkStatus(Request $request)
    {
        $jobId = $request->query('job_id');

        if ($jobId) {
            $job = ImportJob::find($jobId);
        } else {
            $job = ImportJob::whereIn('status', [
                ImportJob::STATUS_VALIDATING,
                ImportJob::STATUS_PENDING,
                ImportJob::STATUS_PROCESSING,
            ])->orderByDesc('created_at')->first();

            if (!$job) {
                $job = ImportJob::orderByDesc('created_at')->first();
            }
        }

        if (!$job) {
            return response()->json([
                'has_job' => false,
            ]);
        }

        return response()->json([
            'has_job' => true,
            'job_id' => $job->id,
            'status' => $job->status,
            'total_rows' => $job->total_rows,
            'processed_rows' => $job->processed_rows,
            'progress' => $job->progress(),
            'error' => $job->error,
            'is_finished' => $job->isFinished(),
            'is_locked' => $job->is_locked,
            'created_at' => $job->created_at->format('d.m.Y H:i'),
            'updated_at' => $job->updated_at->format('d.m.Y H:i'),
        ]);
    }

    /**
     * AJAX: Import job'ı iptal et
     */
    public function cancelImport(Request $request)
    {
        $jobId = $request->input('job_id');

        $job = $jobId
            ? ImportJob::find($jobId)
            : ImportJob::whereIn('status', [
                ImportJob::STATUS_VALIDATING,
                ImportJob::STATUS_PENDING,
                ImportJob::STATUS_PROCESSING
            ])->first();

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Aktif import job bulunamadı.',
            ]);
        }

        if ($job->isFinished()) {
            return response()->json([
                'success' => false,
                'message' => 'Bu job zaten tamamlanmış.',
            ]);
        }

        $job->markAsCancelled();

        app(AdminLogService::class)->log(
            'Toplu Excel İşlemi İptal Edildi',
            null,
            ['job_id' => $job->id, 'processed_rows' => $job->processed_rows]
        );

        return response()->json([
            'success' => true,
            'message' => 'Import işlemi iptal edildi.',
        ]);
    }

    public function restore(ProductBackup $backup)
    {
        $activeJob = ImportJob::whereIn('status', [
            ImportJob::STATUS_VALIDATING,
            ImportJob::STATUS_PENDING,
            ImportJob::STATUS_PROCESSING
        ])->exists();

        if ($activeJob) {
            return back()->withErrors([
                'msg' => 'Devam eden bir import işlemi var. Lütfen bekleyin.'
            ]);
        }

        $result = $this->service->restoreFromBackup($backup);

        if (!$result['success']) {
            app(AdminLogService::class)->log(
                'Excel Yedek Geri Yükleme Başarısız',
                ['backup_id' => $backup->id],
                ['error' => $result['message']]
            );

            return back()
                ->withErrors(['msg' => $result['message']])
                ->with('import_errors', $result['errors']);
        }

        app(AdminLogService::class)->log(
            'Excel Yedek Geri Yükleme Başarılı',
            ['backup_id' => $backup->id],
            ['updated' => $result['updated']]
        );

        return back()
            ->with('success', 'Seçilen yedek geri yüklendi. Güncellenen kayıt: ' . $result['updated'])
            ->with('import_errors', $result['errors']);
    }

    public function verifyPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $userId = session('admin_user_id');
        $user = User::find($userId);

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['success' => false, 'msg' => 'Hatalı şifre.']);
        }

        return response()->json(['success' => true]);
    }
}