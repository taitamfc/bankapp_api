<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\App;
use App\Http\Resources\AppResource;

class AppController extends Controller
{
    public function index()
    {
        $data = AppResource::collection(App::all());
        $res = [
            'success' => true,
            'data' => $data,
        ];
        return $res;
    }
    public function show(string $id)
    {
        $data = new AppResource(App::findOrFail($id));
        $res = [
            'success' => true,
            'data' => $data,
        ];
        return $res;
    }
}
