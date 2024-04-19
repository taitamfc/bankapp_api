<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillGenerate extends Model
{
    /*
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
    */
    use HasFactory;
    // Convert N sang thứ
    public static function convertThu($dateString){
        $date = strtotime($dateString);
        $dayOfWeek = date('N', $date);
        $days = array(
            1 => 'Thứ hai',
            2 => 'Thứ ba',
            3 => 'Thứ tư',
            4 => 'Thứ năm',
            5 => 'Thứ sáu',
            6 => 'Thứ bảy',
            7 => 'Chủ nhật'
        );
        $formattedDate = $days[$dayOfWeek];
        return $formattedDate;
    }
    // VCB
    public static function generateBillVCB($data){
        unset($data['so_tien_bang_chu']);
        $font = 'Arial';
        // Font chữ
        $fontSizes = [
            100,//số tiền
            55,//thời gian chuyển
            55,//tên người thụ hưởng
            55,//ngân hàng thụ hưởng
            55,//mã giao dịch
            55,//nội dung
        ];
        // Màu chữ
        $textColors = [
            '#72BF00',//số tiền
            '#858E92',//thời gian chuyển
            '#FFFFFF',//tên người thụ hưởng
            '#FFFFFF',//ngân hàng thụ hưởng
            '#FFFFFF',//mã giao dịch
            '#FFFFFF',//nội dung
        ];
        $thoi_gian_chuyen = $data['gio_chuyen'].' '.self::convertThu($data['ngay_chuyen']).' '.$data['ngay_chuyen'];
        
        // Ghép với mã ngân hàng và chuyển thành 3 hàng
        $text = $data['ngan_hang_nguoi_nhan'].' ('.$data['ma_ngan_hang_nguoi_nhan'].')';
        $words = explode(" ", $text);
        $lines = array_chunk($words, 3);
        $ngan_hang_thu_huong = "";
        foreach ($lines as $line) {
            $ngan_hang_thu_huong .= implode(" ", $line) . "\n";
        }

        $texts = [
            $data['so_tien'], //số tiền
            $thoi_gian_chuyen,//thời gian chuyển
            $data['ten_nguoi_nhan'],//tên người thụ hưởng
            $ngan_hang_thu_huong,//ngân hàng thụ hưởng
            $data['ma_giao_dich'],//mã giao dịch
            $data['noi_dung'],//nội dung
        ];
        // Vị trí chữ
        $positions = [
            ['x' => 500, 'y' => 950],//số tiền
            ['x' => 455, 'y' => 1060],//thời gian chuyển
            ['x' => 455, 'y' => 1250],//tên người thụ hưởng
            ['x' => 455, 'y' => 1560],//ngân hàng thụ hưởng
            ['x' => 455, 'y' => 1760],//mã giao dịch
            ['x' => 455, 'y' => 1960],//nội dung
        ];
        return [ $font, $fontSizes, $textColors, $texts, $positions ];
    }
}
