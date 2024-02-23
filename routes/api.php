<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BankController;
use App\Http\Controllers\Api\UserBankController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\ChangePassWordController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\NewController;
use App\Http\Controllers\Api\OwnerBankController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Bank
Route::apiResource('banks', BankController::class);

// User Bank
Route::post('userbank/store',[UserBankController::class,'store']);
Route::put('user_banks/{bank_id}',[UserBankController::class,'update']);


// User
Route::get('users/profile', [UserController::class,'show']);
Route::put('users/profile', [UserController::class,'update']);
// Transaction
Route::post('transactions/history',[TransactionController::class,'index']);

// Forgot password
Route::post('forgot-password', [AuthController::class,'sendMailResetPassword']);

// PaymentDeposit
Route::post('deposits', [TransactionController::class,'deposits']);
Route::post('payments/deposits', [TransactionController::class,'listDeposits']);

// PaymentEarnMoney
Route::get('payment/earn-money', [TransactionController::class,'indexEarnMoney']);
Route::post('payments/withdraw', [TransactionController::class,'withdraw']);
Route::post('payments/withdraws', [TransactionController::class,'paymentWithdraw']);
Route::post('sendmail-earnmoney', [TransactionController::class,'sendMailEarnMoney']);
Route::post('sendmail-transfer', [TransactionController::class,'sendMailTransfer']);
Route::post('payments/transfer', [TransactionController::class,'transfer']);

// Home 
Route::get('home', [HomeController::class,'index']);

// News
Route::get('news', [NewController::class,'index']);

// Owner Bank
Route::get('owner_banks', [OwnerBankController::class,'index']);

//Auth
Route::controller(AuthController::class)->group(function () {
    Route::post('/auth/login', 'login');
    Route::post('/auth/register', 'register');
    Route::post('/auth/logout', 'logout');
    Route::post('/auth/refresh', 'refresh');
    Route::post('/auth/change-password','changePassword');
    Route::post('/auth/change-second-password','secondpassword');
    Route::post('/auth/sendmail-password-otp','sendMailPassOtp');
    Route::post('/auth/sendmail-second-password','sendMailSecondPass');
    Route::post('/auth/reset-password-otp','resetPasswordOtp');
    Route::post('/auth/reset-second-password-otp','resetSecondPasswordOtp');
});
