<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\UserBankAccountResource;
use App\Models\UserBankAccountModel;
class BankAccountController extends Controller
{
    public function getbankVietqr(Request $request) {
        $data = UserBankAccountResource::collection(UserBankAccountModel::all());
        $res = [
            'success' => true,
            'data' => $data,
        ];
        return $res;
    }
}
