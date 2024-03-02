<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\UserBankAccountResource;
use App\Models\UserBankAccount;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class BankAccountController extends Controller
{
    public function getbankVietqr(Request $request)
    {
        $user = User::find(Auth::guard('api')->id());
        $items = UserBankAccount::where('user_id', Auth::guard('api')->id())
            ->where('type', $request->type)
            ->first();
        $items->setAttribute('phone', $user->phone);
        $res = [
            'success' => true,
            'data' => $items,
        ];
        return $res;
    }
}
