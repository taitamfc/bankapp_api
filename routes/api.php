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
use App\Http\Controllers\Api\BankListController;
use App\Http\Controllers\Api\BankAccountController;
use App\Http\Controllers\Api\TransactionAppController;

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
Route::post('userbanks/store',[UserBankController::class,'store']);
Route::put('userbanks/update',[UserBankController::class,'update']);


// User
Route::get('users', [UserController::class,'index']);
Route::get('users/profile', [UserController::class,'show']);
Route::get('users/{id}', [UserController::class,'showUserOfAdmin']);
Route::put('users/profile', [UserController::class,'update']);
Route::post('users/change-password',[UserController::class,'changePassword']);
Route::post('users/send-otp/change-password',[UserController::class,'sendMailOtpchangePass']);
Route::post('users/restore-password',[UserController::class,'restorePassword']);
Route::post('users/update-balance',[UserController::class,'updateBlanceUser']);
Route::post('users/update-role',[UserController::class,'updateRole']);
Route::delete('users/{id}',[UserController::class,'delete']);

// Transaction
Route::get('transactions/history',[TransactionController::class,'index']);
Route::get('transactions/{id}',[TransactionController::class,'show']);
Route::post('transactions/updateStatus/{id}',[TransactionController::class,'updateStatus']);

// Forgot password
Route::post('auth/forgot-password', [AuthController::class,'sendMailResetPassword']);

// PaymentDeposit
Route::post('payments/deposits', [TransactionController::class,'deposits']);
Route::get('payments/deposits', [TransactionController::class,'listDeposits']);
Route::get('payments/deposits/{id}', [TransactionController::class,'depositsDetail']);

// PaymentEarnMoney
Route::get('payments/earn-money', [TransactionController::class,'indexEarnMoney']);
Route::post('payments/withdraw', [TransactionController::class,'withdraw']);
Route::get('payments/withdraws', [TransactionController::class,'paymentWithdraw']);
Route::post('payments/transfer', [TransactionController::class,'transfer']);

// Verify code
Route::post('verify-code/send-otp',[VerifyCodeController::class,'sendOTP']);


// Home 
Route::get('home', [HomeController::class,'index']);
Route::get('home/headers', [HomeController::class,'dataHeader']);

// News
Route::get('news', [NewController::class,'index']);

// Owner Bank
Route::get('owner_banks', [OwnerBankController::class,'index']);
Route::get('deposit/qr', [OwnerBankController::class,'getQRdeposit']);

// bank list
Route::get('bank-list', [BankListController::class,'index']);
Route::get('bank-list/{id}', [BankListController::class,'show']);

// User bank account
Route::get('app/user-bank-account', [BankAccountController::class,'getbankVietqr']);
Route::post('app/user-bank-account', [BankAccountController::class,'checkAcountBank']);

//Transaction App
Route::get('app/transactions', [TransactionAppController::class,'index']);
Route::post('app/transfer', [TransactionAppController::class,'transfer']);
Route::post('tranctions-app/deposit', [TransactionAppController::class,'depositApp']);


//Auth
Route::controller(AuthController::class)->group(function () {
    Route::post('/auth/login', 'login');
    Route::post('/auth/register', 'register');
    Route::post('/auth/logout', 'logout');
    Route::post('/auth/refresh', 'refresh');
});