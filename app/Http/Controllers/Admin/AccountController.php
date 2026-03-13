<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    public function index()
    {
        $user = session('admin_user_id') ? User::find(session('admin_user_id')) : null;
        abort_unless($user, 403);
        return view('admin.pages.account.index', compact('user'));
    }

    public function update(Request $request)
    {
        $user = session('admin_user_id') ? User::find(session('admin_user_id')) : null;
        abort_unless($user, 403);
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'max:255', 'unique:users,email,'.$user->id, 'special_characters'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed', 'special_characters'],
        ]);
        if ($validator->fails()) {
            return $this->jsonValidationError($validator->errors()->toArray(), implode(' ', $validator->errors()->all()));
        }
        $data = $validator->validated();
        $user->email = $data['email'];
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();
        return $this->jsonSuccess('Hesap güncellendi');
    }
}


