<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\User;
use App\Http\Resources\PackageResource;
class PackageController extends Controller
{
    public function index()
    {
        $data = PackageResource::collection(Package::all());
        $res = [
            'success' => true,
            'data' => $data,
        ];
        return $res;
    }

    public function buyPackage (Request $request) 
    {
        
    }

}
