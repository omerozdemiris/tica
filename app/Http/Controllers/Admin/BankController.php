<?php

namespace App\Http\Controllers\Admin;

use App\Models\Bank;
use App\Models\Store;
use App\Services\Logs\AdminLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BankController extends Controller
{
    public function store(Request $request)
    {
        $this->validate($request, [
            'bank_name' => 'required|string|max:255|special_characters',
            'bank_iban' => 'required|string|max:255|special_characters',
            'bank_receiver' => 'required|string|max:255|special_characters',
            'status' => 'nullable|boolean',
        ]);
        $validator = Validator::make($request->all(), $this->rules(), $this->messages());

        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray(), implode(' ', $validator->errors()->all()));
        }

        $store = Store::first();
        if (!$store) {
            return $this->jsonError('Mağaza bilgisi bulunamadı. Lütfen önce mağaza ayarlarını kaydedin.');
        }

        $data = $validator->validated();
        $data['status'] = (bool) ($data['status'] ?? false);

        $bank = $store->banks()->create($data);

        app(AdminLogService::class)->log('Banka Bilgisi Eklendi', null, $bank->toArray());

        return $this->jsonSuccess('Banka bilgisi eklendi.', [
            'bank' => $bank,
        ]);
    }

    public function update(Request $request, Bank $bank)
    {
        $before = $bank->toArray();
        $validator = Validator::make($request->all(), $this->rules(), $this->messages());

        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray(), implode(' ', $validator->errors()->all()));
        }

        $data = $validator->validated();
        $data['status'] = (bool) ($data['status'] ?? false);

        $bank->update($data);

        app(AdminLogService::class)->log('Banka Bilgisi Güncellendi', $before, $bank->fresh()->toArray());

        return $this->jsonSuccess('Banka bilgisi güncellendi.', [
            'bank' => $bank->fresh(),
        ]);
    }

    public function destroy(Bank $bank)
    {
        $before = $bank->toArray();
        $bank->delete();

        app(AdminLogService::class)->log('Banka Bilgisi Silindi', $before, null);

        return $this->jsonSuccess('Banka bilgisi silindi.');
    }

    public function toggleStatus(Request $request, Bank $bank)
    {
        $before = $bank->toArray();
        $status = $request->boolean('status');
        $bank->update([
            'status' => $status,
        ]);

        app(AdminLogService::class)->log('Banka Durumu Güncellendi', $before, $bank->fresh()->toArray());

        return $this->jsonSuccess('Banka durumu güncellendi.', [
            'status' => $bank->status,
        ]);
    }

    protected function rules(): array
    {
        return [
            'bank_name' => ['required', 'string', 'max:255'],
            'bank_iban' => ['required', 'string', 'max:255'],
            'bank_receiver' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'boolean'],
        ];
    }

    protected function messages(): array
    {
        return [
            'bank_name.required' => 'Banka adı zorunludur.',
            'bank_iban.required' => 'IBAN alanı zorunludur.',
            'bank_receiver.required' => 'Alıcı adı zorunludur.',
        ];
    }
}
