<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UserUpdateRequest;


class UserController extends Controller
{
    public function show(string $id)
    {
        $data = new UserResource(User::findOrFail($id));
        $res = [
            'success' => true,
            'data' => $data,
        ];
        return $res;
    }

    public function update(UserUpdateRequest $request,$id)
    {
        $user = User::findOrFail($id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->password = Hash::make($request->password);
        $user->referral_code = $request->referral_code;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user
        ]);
    }

}
