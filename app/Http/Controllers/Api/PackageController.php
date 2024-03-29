<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\User;
use App\Http\Resources\PackageResource;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Transaction;
use DB;

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
        DB::beginTransaction();
        try {
            $package = Package::where('type',$request->type_package)->first();
            $today = Carbon::now();
            $user = Auth::guard('api')->user();
            if($user->type_package == null) {
                if ($user->account_balance < $package->price) {
                    $res = [
                        'success' => false,
                        'message' => 'Bạn Không đủ tiền mua gói!',
                    ];
                    return $res;
                }
                $user->type_package = $request->type_package;
                $user->start_day = $today;
                $user->end_day = $today->copy()->addDays(30);
                $user->account_balance -= $package->price;
                $user->save();
                // lưu vào lịch sử
                $transaction = new Transaction;
                $transaction->reference = intval(substr(strval(microtime(true) * 10000), -6));
                $transaction->amount = $package->price;
                $transaction->received = $package->price;
                $transaction->note = 'Mua gói '.$package->name;
                $transaction->type = 'BUYPACKAGE';
                $transaction->type_money = 'VND';
                $transaction->status = 0;
                $transaction->user_id = $user->id;
                $transaction->save();

                DB::commit();
                $res = [
                    'success' => true,
                    'data' => $user,
                    'transaction' => $transaction,
                ];
                return $res;
            }else{
                $res = [
                    'success' => false,
                    'message' => 'Tài khoản đã mua gói',
                ];
                return $res;
            }
            
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }

}
