<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\BankResource;
use App\Models\Bank;

class BankController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = BankResource::collection(Bank::all());
        $res = [
            'success' => true,
            'data' => $data,
        ];
        return $res;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = new BankResource(Bank::findOrFail($id));
        $res = [
            'success' => true,
            'data' => $data,
        ];
        return $res;
    }

}
