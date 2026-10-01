<?php

namespace App\Http\Controllers\Admin;

use App\Models\Attribute;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TermController extends Controller
{
    public function index()
    {
        $terms = Term::with('attribute')->latest()->paginate(20);
        $attributes = Attribute::orderBy('name')->get();
        return view('admin.pages.terms.index', compact('terms', 'attributes'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'attribute_id' => ['required', 'integer', 'exists:attributes,id'],
            'name' => ['required', 'string', 'max:255', 'special_characters'],
            'value' => ['nullable', 'string', 'max:255', 'special_characters'],
        ]);
        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray(), implode(' ', $validator->errors()->all()));
        }
        Term::create($validator->validated());
        return $this->jsonSuccess('Terim oluşturuldu');
    }

    public function update(Request $request, int $id)
    {
        $term = Term::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'attribute_id' => ['required', 'integer', 'exists:attributes,id'],
            'name' => ['required', 'string', 'max:255', 'special_characters'],
            'value' => ['nullable', 'string', 'max:255', 'special_characters'],
        ]);
        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray(), implode(' ', $validator->errors()->all()));
        }
        $term->update($validator->validated());
        return $this->jsonSuccess('Terim güncellendi');
    }

    public function destroy(int $id)
    {
        $term = Term::findOrFail($id);
        $term->delete();
        return $this->jsonSuccess('Terim silindi');
    }
}


