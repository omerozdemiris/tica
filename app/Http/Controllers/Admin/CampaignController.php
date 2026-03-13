<?php

namespace App\Http\Controllers\Admin;

use App\Models\Campaign;
use App\Services\Logs\AdminLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::latest()->paginate(20);
        $colors = [
            'Siyah' => '#000000',
            'Koyu Gri' => '#111827',
            'Gri' => '#1f2937',
            'Açık Gri' => '#374151',
            'Orta Gri' => '#4b5563',
            'Gümüş' => '#6b7280',
            'Açık Gümüş' => '#9ca3af',
            'Çok Açık Gri' => '#d1d5db',
            'Beyazımsı' => '#f3f4f6',
            'Beyaz' => '#ffffff',
            'Kırmızı' => '#ef4444',
            'Koyu Kırmızı' => '#dc2626',
            'Pembe' => '#ec4899',
            'Turuncu' => '#f97316',
            'Sarı' => '#eab308',
            'Altın' => '#fbbf24',
            'Yeşil' => '#22c55e',
            'Koyu Yeşil' => '#16a34a',
            'Mavi' => '#3b82f6',
            'Koyu Mavi' => '#2563eb',
            'Lacivert' => '#1e40af',
            'Mor' => '#a855f7',
            'Koyu Mor' => '#9333ea',
            'Turkuaz' => '#06b6d4',
            'Cyan' => '#0891b2',
        ];
        return view('admin.pages.campaigns.index', compact('campaigns', 'colors'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'title' => ['required', 'string', 'max:255', 'special_characters'],
                'link' => ['nullable', 'string', 'max:255', 'special_characters'],
                'section' => ['required', 'in:header,footer'],
                'background_color' => ['nullable', 'string', 'max:20'],
            ],
            [
                'title.required' => 'Kampanya başlığı zorunludur.',
                'title.max' => 'Kampanya başlığı 255 karakterden uzun olamaz.',
                'link.max' => 'Link alanı 255 karakterden uzun olamaz.',
                'section.required' => 'Kampanya konumu seçilmelidir.',
                'section.in' => 'Kampanya konumu yalnızca header veya footer olabilir.',
                'background_color.max' => 'Arkaplan rengi 20 karakterden uzun olamaz.',
            ]
        );
        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray(), implode(' ', $validator->errors()->all()));
        }
        $data = $validator->validated();
        $exists = Campaign::where('section', $data['section'])->exists();
        if ($exists) {
            return $this->jsonValidationError(
                ['section' => ['Bu konum için zaten aktif bir kampanya bulunuyor.']],
                'Bu konum için zaten aktif bir kampanya bulunuyor.'
            );
        }
        $item = Campaign::create($data);

        app(AdminLogService::class)->log('Duyuru Çubuğu Oluşturuldu', null, $item->toArray());

        return $this->jsonSuccess('Kampanya oluşturuldu', ['id' => $item->id]);
    }

    public function update(Request $request, int $id)
    {
        $item = Campaign::findOrFail($id);
        $before = $item->toArray();
        $validator = Validator::make(
            $request->all(),
            [
                'title' => ['required', 'string', 'max:255', 'special_characters'],
                'link' => ['nullable', 'string', 'max:255', 'special_characters'],
                'section' => ['required', 'in:header,footer'],
                'background_color' => ['nullable', 'string', 'max:20'],
            ],
            [
                'title.required' => 'Kampanya başlığı zorunludur.',
                'title.max' => 'Kampanya başlığı 255 karakterden uzun olamaz.',
                'link.max' => 'Link alanı 255 karakterden uzun olamaz.',
                'section.required' => 'Kampanya konumu seçilmelidir.',
                'section.in' => 'Kampanya konumu yalnızca header veya footer olabilir.',
                'background_color.max' => 'Arkaplan rengi 20 karakterden uzun olamaz.',
            ]
        );
        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray(), implode(' ', $validator->errors()->all()));
        }
        $data = $validator->validated();
        if ($item->section !== $data['section']) {
            $exists = Campaign::where('section', $data['section'])->exists();
            if ($exists) {
                return $this->jsonValidationError(
                    ['section' => ['Bu konum için zaten aktif bir kampanya bulunuyor.']],
                    'Bu konum için zaten aktif bir kampanya bulunuyor.'
                );
            }
        }
        $item->update($data);

        app(AdminLogService::class)->log('Duyuru Çubuğu Güncellendi', $before, $item->fresh()->toArray());

        return $this->jsonSuccess('Kampanya güncellendi');
    }

    public function destroy(int $id)
    {
        $item = Campaign::findOrFail($id);
        $before = $item->toArray();
        $item->delete();

        app(AdminLogService::class)->log('Duyuru Çubuğu Silindi', $before, null);

        return $this->jsonSuccess('Kampanya silindi');
    }
}
