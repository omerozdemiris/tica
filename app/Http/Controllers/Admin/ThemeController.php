<?php

namespace App\Http\Controllers\Admin;

use App\Models\Theme;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ThemeController extends Controller
{
    public function selection()
    {
        $templates = Template::all();
        $theme = Theme::first();
        return view('admin.pages.theme.selection', compact('templates', 'theme'));
    }

    public function updateSelection(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'path' => 'required|exists:templates,path',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'msg' => 'Geçersiz tema seçimi.',
                    'errors' => $validator->errors(),
                    'code' => 2,
                ], 422);
            }

            $theme = Theme::first() ?? new Theme();
            $theme->thene = $request->path;
            $theme->save();

            return $this->jsonSuccess('Tema başarıyla değiştirildi.');
        } catch (\Throwable $exception) {
            return response()->json([
                'msg' => 'Tema seçilirken bir hata oluştu.',
                'code' => 2,
            ], 500);
        }
    }

    public function index()
    {
        $theme = Theme::first() ?? new Theme();
        $colors = config('theme.colors');

        return view('admin.pages.theme.index', compact('theme', 'colors'));
    }

    public function update(Request $request)
    {
        try {
            $colorKeys = array_keys(config('theme.colors'));

            $validator = Validator::make($request->all(), [
                'color' => ['required', Rule::in($colorKeys)],
            ]);

            if ($validator->fails()) {
                $messages = implode(' ', $validator->errors()->all());

                return response()->json([
                    'msg' => $messages,
                    'errors' => $validator->errors(),
                    'code' => 2,
                ], 422);
            }

            $selectedKey = $request->input('color');
            $colorConfig = config('theme.colors.' . $selectedKey);

            $theme = Theme::first() ?? new Theme();

            if (!empty($colorConfig['custom']) && $colorConfig['custom'] === true) {
                $theme->color = '[' . $colorConfig['hex'] . ']';
            } else {
                $theme->color = substr($selectedKey, 3);
            }

            $theme->save();

            return $this->jsonSuccess('Tema ayarları güncellendi', [
                'color' => $theme->color,
            ]);
        } catch (\Throwable $exception) {
            Log::error('Theme update failed', [
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'msg' => 'Tema ayarları güncellenirken bir hata oluştu.',
                'errors' => [
                    'exception' => [$exception->getMessage()],
                ],
                'code' => 2,
            ], 500);
        }
    }
}
