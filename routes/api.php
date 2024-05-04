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
use App\Http\Controllers\Api\HomeAdminController;
use App\Http\Controllers\Api\VietqrBankController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\BankInfoBillController;
use App\Http\Controllers\Api\BankNameBillController;
use App\Http\Controllers\Api\PackageBillController;


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
Route::post('users/deposit/handmade', [UserController::class,'depositAppHandmade']);
Route::post('users/update-status', [UserController::class,'updateStatus']);
Route::post('users/admin-update', [UserController::class,'adminUpdate']);
Route::post('users/update-email', [UserController::class,'updateEmail']);
Route::post('users/fee-dowload-bill', [UserController::class,'handleFeeDowloadBill']);

// Transaction
Route::get('transactions/history',[TransactionController::class,'index']);
Route::get('transactions/history-admin',[TransactionController::class,'historyAdmin']);
Route::get('transactions/{id}',[TransactionController::class,'show']);
Route::post('transactions/updateStatus/{id}',[TransactionController::class,'updateStatus']);

// Forgot password
Route::post('auth/forgot-password', [AuthController::class,'sendMailResetPassword']);

// Home Admin
Route::get('home/admin', [HomeAdminController::class,'index']);

// PaymentDeposit
Route::post('payments/deposits', [TransactionController::class,'deposit']);
Route::get('payments/deposits', [TransactionController::class,'depositHistory']);
Route::get('payments/deposits/{id}', [TransactionController::class,'depositsDetail']);

// PaymentEarnMoney
Route::get('payments/earn-money', [TransactionController::class,'indexEarnMoney']);
Route::post('payments/withdraw', [TransactionController::class,'withdraw']);
Route::get('payments/withdraws', [TransactionController::class,'withdrawHistory']);
Route::post('payments/transfer', [TransactionController::class,'transfer']);

// Verify code
Route::post('verify-code/send-otp',[VerifyCodeController::class,'sendOTP']);


// Home 
Route::get('home', [HomeController::class,'index']);
Route::get('home/headers', [HomeController::class,'dataHeader']);

// News
Route::get('news', [NewController::class,'index']);
Route::get('news/getall', [NewController::class,'getAllNews']);
Route::post('news/create', [NewController::class,'store']);
Route::post('news', [NewController::class,'update']);
Route::get('news/show', [NewController::class,'show']);
Route::delete('news/{id}', [NewController::class,'delete']);

// Owner Bank
Route::get('owner_banks', [OwnerBankController::class,'index']);
Route::get('deposit/qr', [OwnerBankController::class,'getQRdeposit']);

// bank list
Route::get('bank-list', [BankListController::class,'index']);
Route::get('bank-list/{id}', [BankListController::class,'show']);

// package
Route::get('package', [PackageController::class,'index']);
Route::post('package/buy', [PackageController::class,'buyPackage']);


// User bank account
Route::get('app/user-bank-account/all', [BankAccountController::class,'index']);
Route::get('app/user-bank-account', [BankAccountController::class,'getbankVietqr']);
Route::get('app/user-bank-accounts', [BankAccountController::class,'getAllAccountUser']);
Route::post('app/user-bank-account', [BankAccountController::class,'checkAcountBank']);
Route::put('app/user-bank-account', [BankAccountController::class,'updateAcountBank']);
Route::post('app/checkVietQr', [BankAccountController::class,'checkVietQrBank']);
Route::delete('app/user-bank-account/{id}',[BankAccountController::class,'delete']);
Route::get('app/user-bank-account/get-admin', [BankAccountController::class,'getAccountBankUser']);

// bank info bill
Route::get('bankinfobills', [BankInfoBillController::class,'index']);

// bank name bill
Route::get('bank-name-bill', [BankNameBillController::class,'index']);


//Transaction App
Route::get('app/transactions', [TransactionAppController::class,'index']);
Route::post('app/transfer', [TransactionAppController::class,'transfer']);
Route::post('tranctions-app/deposit', [TransactionAppController::class,'depositApp']);

// vietqr all bank
Route::get('vietqr-bank-all', [VietqrBankController::class,'index']);

// package bill
Route::get('package-bill', [PackageBillController::class,'index']);
Route::post('package-bill/buy', [PackageBillController::class,'buyPackageBill']);


//Auth
Route::controller(AuthController::class)->group(function () {
    Route::post('/auth/login', 'login');
    Route::post('/auth/register', 'register');
    Route::post('/auth/logout', 'logout');
    Route::post('/auth/refresh', 'refresh');
});