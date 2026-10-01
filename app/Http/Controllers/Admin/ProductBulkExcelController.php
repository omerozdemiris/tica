<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Controller;
use App\Services\ProductBulkExcelService;
use App\Models\ProductBackup;
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

        return view('admin.pages.products.bulk_excel', [
            'backups' => $backups,
        ]);
    }

    public function download()
    {
        return $this->service->downloadTemplate();
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:' . (ProductBulkExcelService::MAX_FILE_SIZE / 1024)],
            'label' => ['nullable', 'string', 'max:100'],
        ]);

        $result = $this->service->importAndUpdate(
            $request->file('file'),
            $request->string('label')->toString()
        );

        if (!$result['success']) {
            app(AdminLogService::class)->log('Toplu Excel Yükleme Başarısız', null, ['error' => $result['message'], 'label' => $request->label]);
            return back()
                ->withErrors(['msg' => $result['message']])
                ->with('import_errors', $result['errors'])
                ->withInput();
        }

        app(AdminLogService::class)->log('Toplu Excel Yükleme Başarılı', null, ['updated' => $result['updated'], 'label' => $request->label]);

        return back()
            ->with('success', $result['message'] . ' Güncellenen kayıt: ' . $result['updated'])
            ->with('import_errors', $result['errors']);
    }

    public function restore(ProductBackup $backup)
    {
        $result = $this->service->restoreFromBackup($backup);

        if (!$result['success']) {
            app(AdminLogService::class)->log('Excel Yedek Geri Yükleme Başarısız', ['backup_id' => $backup->id], ['error' => $result['message']]);
            return back()
                ->withErrors(['msg' => $result['message']])
                ->with('import_errors', $result['errors']);
        }

        app(AdminLogService::class)->log('Excel Yedek Geri Yükleme Başarılı', ['backup_id' => $backup->id], ['updated' => $result['updated']]);

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
