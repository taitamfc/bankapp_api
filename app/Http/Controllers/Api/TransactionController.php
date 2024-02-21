<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Http\Resources\TransactionResource;


class TransactionController extends Controller
{
    public function index()
    {
        $data = TransactionResource::collection(Transaction::all());
        $res = [
            'success' => true,
            'data' => $data,
        ];
        return $res;
    }
}
