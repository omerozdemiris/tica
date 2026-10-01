<?php

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use App\Services\Logs\AdminLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SiteSettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::first();
        return view('admin.pages.settings.site.index', ['settings' => $settings ?? new Setting()]);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'logo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:5120'],
            'white_logo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:5120'],
            'favicon_file' => ['nullable', 'mimes:ico,png,svg,jpg,jpeg,webp,gif', 'max:1024'],
            'title' => ['nullable', 'string', 'max:255', 'special_characters'],
            'email' => ['nullable', 'string', 'max:255', 'email'],
            'notify_mail' => ['nullable', 'string', 'max:255', 'email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:255', 'url', 'special_characters'],
            'twitter' => ['nullable', 'string', 'max:255', 'url', 'special_characters'],
            'facebook' => ['nullable', 'string', 'max:255', 'url', 'special_characters'],
            'youtube' => ['nullable', 'string', 'max:255', 'url', 'special_characters'],
            'linkedin' => ['nullable', 'string', 'max:255', 'url', 'special_characters'],
            'whatsapp' => ['nullable', 'string', 'max:255', 'url', 'special_characters'],
            'google_iframe' => ['nullable', 'string'],
        ]);
        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray(), implode(' ', $validator->errors()->all()));
        }
        $data = $validator->validated();
        $row = Setting::first() ?? new Setting();
        $before = $row->toArray();
        // Handle uploads
        $uploadDir = public_path('upload/site');
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }
        if ($request->hasFile('logo_file')) {
            $file = $request->file('logo_file');
            $name = 'logo_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $name);
            $row->logo = '/upload/site/' . $name;
        }
        if ($request->hasFile('white_logo_file')) {
            $file = $request->file('white_logo_file');
            $name = 'logo_white_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $name);
            $row->white_logo = '/upload/site/' . $name;
        }
        if ($request->hasFile('favicon_file')) {
            $file = $request->file('favicon_file');
            $name = 'favicon_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $name);
            $row->favicon = '/upload/site/' . $name;
        }
        // Basic fields
        if (array_key_exists('title', $data)) $row->title = $data['title'];
        if (array_key_exists('email', $data)) $row->email = $data['email'];
        if (array_key_exists('notify_mail', $data)) $row->notify_mail = $data['notify_mail'];
        if (array_key_exists('phone', $data)) $row->phone = $data['phone'];
        if (array_key_exists('google_iframe', $data)) $row->google_iframe = $data['google_iframe'];
        if (array_key_exists('address', $data)) $row->address = $data['address'];
        foreach (['instagram', 'twitter', 'facebook', 'youtube', 'linkedin', 'whatsapp'] as $s) {
            if (array_key_exists($s, $data)) $row->{$s} = $data[$s];
        }
        $row->save();

        app(AdminLogService::class)->log('Site Ayarları Güncellendi', $before, $row->fresh()->toArray());

        return $this->jsonSuccess('Site ayarları güncellendi');
    }
}
