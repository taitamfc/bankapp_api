<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\BankListResource;
use App\Models\BankList;


class BankListController extends Controller
{
    public function index()
    {
        $data = BankListResource::collection(BankList::all());
        $res = [
            'success' => true,
            'data' => $data,
        ];
        return $res;
    }
    public function show(string $id)
    {
        $data = new BankListResource(BankList::findOrFail($id));
        $res = [
            'success' => true,
            'data' => $data,
        ];
        return $res;
    }
}
