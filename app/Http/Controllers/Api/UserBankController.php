<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserBank;
use App\Http\Requests\UserBankRequest;

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
        $userBank->save();
        $res = [
            'success' => true,
            'data' => $userBank
        ];
        return response()->json($res, 200);
    }

}
