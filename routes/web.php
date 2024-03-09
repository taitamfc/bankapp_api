<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TransactionController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('transactions/handle_return',[TransactionController::class,'handle_return'])->name('transactions.handle_return');
Route::get('transactions/handle_cancel',[TransactionController::class,'handle_cancel'])->name('transactions.handle_cancel');

Route::get('{path?}', function () {
    return view('welcome');
})->where('path', '(?!admin|api|landing)[a-zA-Z0-9-/]+');

