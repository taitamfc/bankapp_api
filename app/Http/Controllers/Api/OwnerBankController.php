<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\OwnerBankResource;
use App\Models\OwnerBank;

class OwnerBankController extends Controller
{
    public function index()
    {
        $data = OwnerBankResource::collection(OwnerBank::paginate(5));
        $res = [
            'success' => true,
            'data' => $data,
        ];
        return $res;
    }
}
