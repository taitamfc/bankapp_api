<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BillGenerate;

class BillGenerateController extends Controller
{
    public function index( Request $request ){
        $bill_template_path = public_path('bills');
        // Thay bằng dữ liệu gửi lên
        $data = [
            'so_tien' => '10000', 
            'so_tien_bang_chu' => '', //Tự động đọc số thành chữ
            'ten_nguoi_gui' => 'NGUYEN KIM THAO',
            'so_tai_khoan_nguoi_gui' => '0771111888444',
            'ten_nguoi_nhan' => 'SEN DO',
            'ngan_hang_nguoi_nhan' => 'Ngân hàng Việt Nam Thịnh Vượng',
            'ma_ngan_hang_nguoi_nhan' => 'VPBANK',
            'so_tai_khoan_nguoi_nhan' => 'SEND0771111888444',
            'ngay_chuyen' => '2023-02-13',
            'gio_chuyen' => '11:19',
            'ma_giao_dich' => '3094995304',
            'noi_dung' => 'NGUYEN KIM THAO chuyen tien',
            'gio_hien_tai' => '09:41',
        ];
    
        $type = $request->type ?? 'VCB';//VCB,TCB
        $platform = $request->platform ?? 'IOS';//IOS,ANDROID
        $platform = strtolower($platform);//ios, android

        // Lấy đường dẫn mẫu in
        $template_path = $bill_template_path.'/'.$type.'/'.$platform.'.png';

        // Chuyển đổi dữ liệu được gửi lên
        $data = $this->formatData($data,$type);

        // Khai báo các biến trả về
        list($font,$fontSizes,$textColors,$texts,$positions) = $this->generateBill($data,$type);

        // Xử lý ảnh
        $Imagick = new \Imagick();
        $Imagick->readImage( $template_path );
        $Imagick->setImageFormat( 'png' );
        $Imagick->setImageCompressionQuality(100);

        // Chuyen mau
        $textColorsFm = [];
        foreach( $textColors as $k => $textColor ){
            $textColorsFm[] = new \ImagickPixel($textColor);
        }
        $textColors = $textColorsFm;

        for ($i = 0; $i < count($texts); $i++) {
            $fontSize = $fontSizes[$i];
            $textColor = $textColors[$i];
            $text = $texts[$i];
            $position = $positions[$i];
            $ImagickDraw = new \ImagickDraw();
            $ImagickDraw->setFont( $font );
            $ImagickDraw->setFontSize( $fontSize );
            $ImagickDraw->setFillColor($textColor);
            $Imagick->annotateImage( $ImagickDraw, $position['x'], $position['y'], 0, $text);
        }
        // Chèn thêm mark

        /* Output */
        header( "Content-Type: image/{$Imagick->getImageFormat()}" );
        echo $Imagick->getImageBlob();
        die();
    }

    private function generateBill($data,$type){
        $functionName = 'generateBill' . $type;
        return BillGenerate::$functionName($data);
    }

    // Xử lý dữ liệu đầu vào
    private function formatData($data,$type = ''){
        $so_tien = $data['so_tien'];

        $data['so_tien'] = number_format($data['so_tien']).' VND';
        $data['so_tien_bang_chu'] = $this->readNumber($so_tien);
        $data['ngay_chuyen'] = date('d/m/Y',strtotime($data['ngay_chuyen']));
        return $data;
    }
    // Đọc số thành chữ
    private function readNumber($value){
        return 'Mười nghìn đồng';
    }
    
    
}
