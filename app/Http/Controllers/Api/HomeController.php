<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News;
class HomeController extends Controller
{
    public function index()
    {
        $news = News::all();
        $data = [
            "count_deposit" => 0,
            "count_paymoney" => 0,
            "total_money_pay" => 0,
            "total_money_check" => 0,
            "count_openbank" => 0,
            "count_bill_paymoney" => 0,
            "count_open_account" => 0,
            "count_bill_surplus" => 0,
            "count_account_chilrent" => 0,
            "count_bill_fluctuations" => 0,
            "count_account_chilrent_deposit" => 0,
            "total_money_create_bill_chilrent" => 0,
            "news" => $news,
        ];
        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
