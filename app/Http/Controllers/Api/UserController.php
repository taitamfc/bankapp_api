<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserResource;

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

    public function update(Request $request)
    {
        $userBank = new User;
        $userBank->user_id = $request->user_id;
        $userBank->bank_id = $request->bank_id;
        $userBank->save();
        $res = [
            'success' => true,
            'data' => $userBank
        ];
        return response()->json($res, 200);
    }
}
