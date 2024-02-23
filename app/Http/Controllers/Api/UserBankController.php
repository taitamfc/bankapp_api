<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserBank;
use App\Http\Requests\UserBankRequest;
use Illuminate\Support\Facades\Auth;

class UserBankController extends Controller
{

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserBankRequest $request)
    {
        $userBank = new UserBank;
        $userBank->user_id = $request->user_id;
        $userBank->bank_id = $request->bank_id;
        $userBank->user_status = 0;
        $userBank->save();
        $res = [
            'success' => true,
            'data' => $userBank
        ];
        return response()->json($res, 200);
    }

    public function update(Request $request,$bank_id)
    {
        $user_bank = UserBank::where('user_id',Auth::id())->where('bank_id', $bank_id)->first();
        $user_bank->user_status = $request->user_status;
        $user_bank->save();
        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thành công!',
            'data' => $user_bank
        ]);
    }

}
