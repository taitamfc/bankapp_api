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
use App\Http\Controllers\Api\VerifyCodeController;

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
Route::post('users/change-password',[UserController::class,'changePassword']);
Route::post('users/send-otp/change-password',[UserController::class,'sendMailOtpchangePass']);
Route::post('users/restore-password',[UserController::class,'restorePassword']);

// Transaction
Route::get('transactions/history',[TransactionController::class,'index']);

// Forgot password
Route::post('forgot-password', [AuthController::class,'sendMailResetPassword']);

// PaymentDeposit
Route::post('deposits', [TransactionController::class,'deposits']);
Route::get('payments/deposits', [TransactionController::class,'listDeposits']);

// PaymentEarnMoney
Route::get('payment/earn-money', [TransactionController::class,'indexEarnMoney']);
Route::post('payments/withdraw', [TransactionController::class,'withdraw']);
Route::get('payments/withdraws', [TransactionController::class,'paymentWithdraw']);
Route::post('payments/transfer', [TransactionController::class,'transfer']);
Route::post('earnmoney/sendmail', [TransactionController::class,'sendMail']);

// Verify code
Route::post('verify-code/send-otp',[VerifyCodeController::class,'sendOTP']);


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
});
