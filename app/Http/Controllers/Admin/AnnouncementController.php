<?php

namespace App\Http\Controllers\Admin;

use App\Models\Announcement;
use App\Services\Logs\AdminLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::latest()->paginate(20);
        return view('admin.pages.announcements.index', compact('announcements'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255', 'special_characters'],
            'image' => ['nullable', 'file', 'image', 'max:2048'],
            'link' => ['nullable', 'string', 'max:255', 'special_characters'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray(), implode(' ', $validator->errors()->all()));
        }
        $data = $validator->validated();
        $payload = [
            'title' => $data['title'],
            'link' => $data['link'] ?? null,
            'is_active' => (bool)($data['is_active'] ?? false),
        ];
        if (!empty($payload['is_active'])) {
            $exists = Announcement::where('is_active', true)->exists();
            if ($exists) {
                return $this->jsonError('Başka bir duyuru aktif. Önce onu pasif yapınız.', 409);
            }
        }
        if ($request->hasFile('image')) {
            $payload['image'] = $this->storeAnnouncementImage($request);
        }
        $item = Announcement::create($payload);

        app(AdminLogService::class)->log('Duyuru Oluşturuldu', null, $item->toArray());

        return $this->jsonSuccess('Duyuru oluşturuldu', ['id' => $item->id]);
    }

    public function update(Request $request, int $id)
    {
        $item = Announcement::findOrFail($id);
        $before = $item->toArray();
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'file', 'image', 'max:2048'],
            'link' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray(), implode(' ', $validator->errors()->all()));
        }
        $data = $validator->validated();
        $payload = [
            'title' => $data['title'],
            'link' => $data['link'] ?? null,
            'is_active' => (bool)($data['is_active'] ?? false),
        ];
        if (!empty($payload['is_active'])) {
            $exists = Announcement::where('is_active', true)->where('id', '<>', $item->id)->exists();
            if ($exists) {
                return $this->jsonError('Başka bir duyuru aktif. Önce onu pasif yapınız.', 409);
            }
        }
        if ($request->hasFile('image')) {
            $payload['image'] = $this->storeAnnouncementImage($request, $item);
        }
        $item->update($payload);

        app(AdminLogService::class)->log('Duyuru Güncellendi', $before, $item->fresh()->toArray());

        return $this->jsonSuccess('Duyuru güncellendi');
    }

    public function destroy(int $id)
    {
        $item = Announcement::findOrFail($id);
        $before = $item->toArray();
        $item->delete();

        app(AdminLogService::class)->log('Duyuru Silindi', $before, null);

        return $this->jsonSuccess('Duyuru silindi');
    }

    protected function storeAnnouncementImage(Request $request, ?Announcement $announcement = null): string
    {
        $dir = public_path('upload/announcements');
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        if ($announcement && $announcement->image) {
            $existingPath = public_path(ltrim($announcement->image, '/'));
            if (is_file($existingPath)) {
                @unlink($existingPath);
            }
        }

        $file = $request->file('image');
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $name = uniqid('announcement_') . '.' . $ext;
        $file->move($dir, $name);

        return '/upload/announcements/' . $name;
    }
}
