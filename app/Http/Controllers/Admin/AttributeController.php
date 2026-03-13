<?php

namespace App\Http\Controllers\Admin;

use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttributeController extends Controller
{
    public function index()
    {
        $attributes = Attribute::latest()->paginate(20);
        return view('admin.pages.attributes.index', compact('attributes'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', 'special_characters'],
        ]);
        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray(), implode(' ', $validator->errors()->all()));
        }
        Attribute::create($validator->validated());
        return $this->jsonSuccess('Nitelik oluşturuldu');
    }

    public function update(Request $request, int $id)
    {
        $attribute = Attribute::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', 'special_characters'],
        ]);
        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray(), implode(' ', $validator->errors()->all()));
        }
        $attribute->update($validator->validated());
        return $this->jsonSuccess('Nitelik güncellendi');
    }

    public function destroy(int $id)
    {
        $attribute = Attribute::findOrFail($id);
        $attribute->delete();
        return $this->jsonSuccess('Nitelik silindi');
    }
}


