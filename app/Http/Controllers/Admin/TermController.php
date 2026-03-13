<?php

namespace App\Http\Controllers\Admin;

use App\Models\Attribute;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TermController extends Controller
{
    public function index()
    {
        $terms = Term::with('attribute')->latest()->get();
        $attributes = Attribute::orderBy('name')->get();
        return view('admin.pages.terms.index', compact('terms', 'attributes'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'attribute_id' => ['required', 'integer', 'exists:attributes,id'],
            'name' => ['required', 'string', 'max:255', 'special_characters'],
            'value' => ['nullable', 'string', 'max:255', 'special_characters'],
            'file' => ['nullable', 'image', 'max:200'],
        ]);
        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray(), implode(' ', $validator->errors()->all()));
        }

        $data = $validator->validated();
        $term = Term::create(collect($data)->except('file')->all());

        if ($request->hasFile('file')) {
            $path = $this->storeTermFile($request->file('file'), $term->name);
            $term->update(['file' => $path]);
        }

        return $this->jsonSuccess('Terim oluşturuldu');
    }

    public function update(Request $request, int $id)
    {
        $term = Term::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'attribute_id' => ['required', 'integer', 'exists:attributes,id'],
            'name' => ['required', 'string', 'max:255', 'special_characters'],
            'value' => ['nullable', 'string', 'max:255', 'special_characters'],
            'file' => ['nullable', 'image', 'max:200'],
            'remove_file' => ['nullable', 'boolean'],
        ]);
        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray(), implode(' ', $validator->errors()->all()));
        }

        $data = $validator->validated();
        $removeFile = (bool) ($data['remove_file'] ?? false);
        $term->update(collect($data)->except(['file', 'remove_file'])->all());

        if ($removeFile && $term->file) {
            $this->deletePublicFileIfExists($term->file);
            $term->update(['file' => null]);
        }

        if ($request->hasFile('file')) {
            if ($term->file) {
                $this->deletePublicFileIfExists($term->file);
            }
            $path = $this->storeTermFile($request->file('file'), $term->name);
            $term->update(['file' => $path]);
        }

        return $this->jsonSuccess('Terim güncellendi');
    }

    public function destroy(int $id)
    {
        $term = Term::findOrFail($id);
        if ($term->file) {
            $this->deletePublicFileIfExists($term->file);
        }
        $term->delete();
        return $this->jsonSuccess('Terim silindi');
    }

    protected function storeTermFile(\Illuminate\Http\UploadedFile $file, string $termName): string
    {
        $slug = Str::slug($termName ?: 'term');
        $dir = 'upload/terms/' . $slug;
        $absDir = public_path($dir);

        if (!File::exists($absDir)) {
            File::makeDirectory($absDir, 0755, true);
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $filename = 'term_' . now()->format('YmdHis') . '_' . Str::random(6) . '.' . $ext;
        $file->move($absDir, $filename);

        return $dir . '/' . $filename;
    }

    protected function deletePublicFileIfExists(string $relativePath): void
    {
        $relativePath = ltrim(str_replace(['\\'], '/', $relativePath), '/');
        $abs = public_path($relativePath);
        if (File::exists($abs)) {
            File::delete($abs);
        }
    }
}


