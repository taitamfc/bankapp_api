<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\NewResource;
use App\Models\News;

class NewController extends Controller
{
    public function index()
    {
        $data = NewResource::collection(News::paginate(5));
        $res = [
            'success' => true,
            'data' => $data,
        ];
        return $res;
    }
}
