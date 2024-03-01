<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\UserBankAccountResource;
use App\Models\UserBankAccount;
class BankAccountController extends Controller
{
    public function getbankVietqr(Request $request)
    {
        $items = UserBankAccount::where('user_id', $request->user_id)
            ->where('type', $request->type)
            ->first();
        $res = [
            'success' => true,
            'data' => $items,
        ];
        return $res;
    }
}
