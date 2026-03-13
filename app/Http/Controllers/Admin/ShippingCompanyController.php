<?php

namespace App\Http\Controllers\Admin;

use App\Models\Shipping;
use App\Models\ShippingCompany;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShippingCompanyController extends Controller
{
    public function index()
    {
        $companies = ShippingCompany::orderBy('name')->get();
        return view('admin.pages.shipping_companies.index', compact('companies'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', 'special_characters'],
            'tracking_link' => ['nullable', 'string', 'max:500', 'special_characters'],
            'is_active' => ['nullable', 'boolean'],
        ], [], [
            'name' => 'Firma adı',
            'tracking_link' => 'Takip bağlantısı',
            'is_active' => 'Aktif durum',
        ]);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray(), implode(' ', $validator->errors()->all()));
        }

        $validated = $validator->validated();

        ShippingCompany::create([
            'name' => $validated['name'],
            'tracking_link' => $validated['tracking_link'] ?? null,
            'is_active' => (bool)($validated['is_active'] ?? true),
        ]);

        return $this->jsonSuccess('Kargo firması eklendi', [
            'redirect' => route('admin.shipping-companies.index'),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $company = ShippingCompany::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255', 'special_characters'],
            'tracking_link' => ['nullable', 'string', 'max:500', 'special_characters'],
            'is_active' => ['nullable', 'boolean'],
        ], [], [
            'name' => 'Firma adı',
            'tracking_link' => 'Takip bağlantısı',
            'is_active' => 'Aktif durum',
        ]);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray(), implode(' ', $validator->errors()->all()));
        }

        $validated = $validator->validated();

        $company->update([
            'name' => $validated['name'],
            'tracking_link' => $validated['tracking_link'] ?? null,
            'is_active' => (bool)($validated['is_active'] ?? false),
        ]);

        return $this->jsonSuccess('Kargo firması güncellendi', [
            'redirect' => route('admin.shipping-companies.index'),
        ]);
    }

    public function destroy(int $id)
    {
        $company = ShippingCompany::findOrFail($id);
        Shipping::where('shipping_company_id', $company->id)->update(['shipping_company_id' => null]);
        $company->delete();

        return $this->jsonSuccess('Kargo firması silindi', [
            'redirect' => route('admin.shipping-companies.index'),
        ]);
    }

    public function orders(int $id)
    {
        $company = ShippingCompany::findOrFail($id);
        $orders = Order::where('status', 'completed')
            ->whereHas('shipping', function ($query) use ($company) {
                $query->where('shipping_company_id', $company->id);
            })
            ->with(['shipping', 'user', 'shippingAddress', 'billingAddress'])
            ->latest()
            ->get();

        return view('admin.pages.shipping_companies.orders', compact('company', 'orders'));
    }
}
