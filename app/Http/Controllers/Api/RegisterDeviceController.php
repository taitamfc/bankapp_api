<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class RegisterDeviceController extends Controller
{
    private $nameFileTxt = "devices_test.txt";

    public function index(Request $request)
    {
        $udid = $request->udid;
        $name = $request->name;

        $data = "$udid $name";
        $filePath = public_path($this->nameFileTxt);
        $currentContent = File::get($filePath);
        if (!empty($currentContent)) {
            $currentContent .= "\n";
            $currentContent = $currentContent . $data;
        } else {
            $currentContent = "Device ID  Device Name";
            $currentContent .= "\n";
            $currentContent = $currentContent . $data;
        }
        File::put($filePath, $currentContent);
        $res = [
            'success' => true,
            'data' => $data,
        ];
        return response()->json($res);
    }

    public function download()
    {
        $file = public_path($this->nameFileTxt);

        if (File::exists($file)) {
            return response()->download($file, $this->nameFileTxt);
        } else {
            abort(404, 'File not found');
        }
    }
}
