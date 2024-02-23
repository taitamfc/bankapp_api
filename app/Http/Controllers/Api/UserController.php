<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UserUpdateRequest;
use Illuminate\Support\Facades\Auth;


class UserController extends Controller
{
    public function show()
    {
        $user_id = Auth::id();
        $data = new UserResource(User::findOrFail($user_id));
        $res = [
            'success' => true,
            'data' => $data,
        ];
        return $res;
    }

    public function update(UserUpdateRequest $request)
    {
        $user_id = Auth::id();
        $user = User::findOrFail($user_id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->password = Hash::make($request->password);
        $user->referral_code = $request->referral_code;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật hồ sơ thành công!',
            'data' => $user
        ]);
    }

}
