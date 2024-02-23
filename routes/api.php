<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BankController;
use App\Http\Controllers\Api\UserBankController;
use App\Http\Controllers\Api\AppController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\ChangePassWordController;
use App\Http\Controllers\Api\HomeController;

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

// App
Route::apiResource('app', AppController::class);

// User
Route::apiResource('user', UserController::class);

// Transaction
Route::post('transactions',[TransactionController::class,'index']);

// Forgot password
Route::post('forgot-password', [AuthController::class,'sendMailResetPassword']);

// PaymentDeposit
Route::post('recharge', [TransactionController::class,'recharge']);
Route::post('list-recharge', [TransactionController::class,'listRecharge']);

// PaymentEarnMoney
Route::get('index-earn-money', [TransactionController::class,'indexEarnMoney']);
Route::post('payment-earn-money', [TransactionController::class,'paymentEarnMoney']);
Route::post('list-earnmoney', [TransactionController::class,'listEarnMoney']);
Route::post('sendmail-earnmoney', [TransactionController::class,'sendMailEarnMoney']);
Route::post('sendmail-paymoney', [TransactionController::class,'sendMailPayMoney']);
Route::post('paymoney', [TransactionController::class,'payMoney']);

// Home 
Route::get('home', [HomeController::class,'index']);

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
