<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserBankAccount;
use App\Models\Transaction;
use App\Models\TransactionApp;
use Carbon\Carbon;

class HomeAdminController extends Controller
{
    public function index(){
        try {
            $total_user = User::count();
            $total_user_active = User::where('status', 1)->count();
            $total_user_NoActive = User::where('status', 0)->count();
            // những người đăng ký hôm nay
            $currentDate = Carbon::now()->toDateString();
            $usersToday = User::whereDate('created_at', $currentDate)->count();
            $data = [
                'total_user' => $total_user,
                'total_user_active' => $total_user_active,
                'total_user_NoActive' => $total_user_NoActive,
                'usersToday' => $usersToday,
            ];
            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
            ]);
        }
    }
}

