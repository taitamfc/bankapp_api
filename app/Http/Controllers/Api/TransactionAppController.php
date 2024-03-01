<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransactionApp;
use Illuminate\Http\Request;
use App\Http\Resources\TransactionAppResource;
use App\Models\UserBankAccount;
use DB;
class TransactionAppController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = TransactionAppResource::collection(TransactionApp::all());
        $res = [
            'success' => true,
            'data' => $data,
        ];
        return $res;
    }

    public function depositApp(Request $request)
    {
        DB::beginTransaction();
        try {
            $user_bank_account = UserBankAccount::findOrFail($request->user_bank_account_id);
            $surplus = $request->amount + $user_bank_account->surplus;
            $user_bank_account->surplus = $surplus;
            $user_bank_account->save();

            $transaction_app_deposit = new TransactionApp;
            $transaction_app_deposit->

            $res = [
                'success' => true,
                'data' => $user_bank_account,
            ];
            return $res;
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(TransactionApp $transactionApp)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TransactionApp $transactionApp)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TransactionApp $transactionApp)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TransactionApp $transactionApp)
    {
        //
    }
}