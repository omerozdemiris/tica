<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PhotoUploadServiceController extends Controller
{
    public function list(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => ['required', 'integer'],
            'parent' => ['required', 'integer'],
        ]);
        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray(), implode(' ', $validator->errors()->all()));
        }
        $data = $validator->validated();
        $items = Document::where('type', $data['type'])->where('parent', $data['parent'])->orderBy('queue')->get();
        $dirs = config('files.dirs', []);
        $payload = $items->map(function ($doc) use ($dirs) {
            return [
                'id' => $doc->id,
                'type' => (int)$doc->type,
                'parent' => (int)$doc->parent,
                'queue' => (int)$doc->queue,
                'name' => $doc->name,
                'original_name' => $doc->original_name,
                'extension' => $doc->extension,
                'dirKey' => $doc->dir,
                'dirFolder' => $dirs[$doc->type] ?? 'misc',
            ];
        });
        return $this->jsonSuccess('ok', ['items' => $payload]);
    }

    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => ['required', 'integer'],
            'parent' => ['required', 'integer'],
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,gif'],
        ]);
        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray(), implode(' ', $validator->errors()->all()));
        }
        $data = $validator->validated();
        $file = $request->file('file');
        $original = $file->getClientOriginalName();
        $ext = strtolower($file->getClientOriginalExtension());

        $dirs = config('files.dirs', []);
        $types = config('files.types', []);
        $typeId = (int)$data['type'];
        $subdir = $dirs[$typeId] ?? 'misc';
        $dirKey = array_search($typeId, $types, true) ?: null;
        $dir = 'upload/' . $subdir;
        if (!is_dir(public_path($dir))) {
            @mkdir(public_path($dir), 0777, true);
        }
        $unique = uniqid('', true) . '.webp';
        $target = public_path($dir . '/' . $unique);

        // Convert to webp (gd) fallback to move
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
            $file->move(public_path($dir), $unique);
        }

        $queue = (int) Document::where('type', $typeId)->where('parent', $data['parent'])->max('queue') + 1;
        $doc = Document::create([
            'type' => $typeId,
            'parent' => (int)$data['parent'],
            'queue' => $queue,
            'name' => $unique,
            'original_name' => $original,
            'extension' => 'webp',
            'dir' => $dirKey,
        ]);
        return $this->jsonSuccess('Yüklendi', ['item' => $doc]);
    }

    public function changeOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'integer', 'exists:documents,id'],
            'queue' => ['required', 'integer', 'min:0'],
        ]);
        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray(), implode(' ', $validator->errors()->all()));
        }
        $data = $validator->validated();
        $doc = Document::findOrFail($data['id']);
        $doc->queue = (int)$data['queue'];
        $doc->save();
        return $this->jsonSuccess('Sıra güncellendi');
    }

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'integer', 'exists:documents,id'],
        ]);
        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray(), implode(' ', $validator->errors()->all()));
        }
        $doc = Document::findOrFail($request->integer('id'));
        $dirs = config('files.dirs', []);
        $subdir = $dirs[$doc->type] ?? 'misc';
        $path = public_path('upload/' . $subdir . '/' . $doc->name);
        if (is_file($path)) {
            @unlink($path);
        }
        $doc->delete();
        return $this->jsonSuccess('Silindi');
    }
}
