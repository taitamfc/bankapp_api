<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\News;
class NewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $news = [
            [
                'title' => 'Vụ xài thẻ tín dụng 8,5 triệu đồng, “ôm” nợ 8,8 tỉ đồng: Con số 8,8 tỉ đồng ở đâu ra?1',
                'content' => '<p>Theo Eximbank, về phương thức t&iacute;nh l&atilde;i, ph&iacute; l&agrave; ho&agrave;n to&agrave;n ph&ugrave; hợp với thỏa thuận giữa Eximbank v&agrave; kh&aacute;ch h&agrave;ng theo hồ sơ mở thẻ ng&agrave;y 15-3-2013 c&oacute; đầy đủ chữ k&yacute; kh&aacute;ch h&agrave;ng (quy định về ph&iacute;, l&atilde;i được quy định r&otilde; trong Biểu ph&iacute; ph&aacute;t h&agrave;nh, sử dụng thẻ đ&atilde; được đăng tải c&ocirc;ng khai tr&ecirc;n website của Eximbank).</p>',
                'status' => 0,
            ],
            [
                'title' => 'Vụ xài thẻ tín dụng 8,5 triệu đồng, “ôm” nợ 8,8 tỉ đồng: Con số 8,8 tỉ đồng ở đâu ra?2',
                'content' => '<p>Theo Eximbank, về phương thức t&iacute;nh l&atilde;i, ph&iacute; l&agrave; ho&agrave;n to&agrave;n ph&ugrave; hợp với thỏa thuận giữa Eximbank v&agrave; kh&aacute;ch h&agrave;ng theo hồ sơ mở thẻ ng&agrave;y 15-3-2013 c&oacute; đầy đủ chữ k&yacute; kh&aacute;ch h&agrave;ng (quy định về ph&iacute;, l&atilde;i được quy định r&otilde; trong Biểu ph&iacute; ph&aacute;t h&agrave;nh, sử dụng thẻ đ&atilde; được đăng tải c&ocirc;ng khai tr&ecirc;n website của Eximbank).</p>',
                'status' => 0,
            ],
            [
                'title' => 'Vụ xài thẻ tín dụng 8,5 triệu đồng, “ôm” nợ 8,8 tỉ đồng: Con số 8,8 tỉ đồng ở đâu ra?3',
                'content' => '<p>Theo Eximbank, về phương thức t&iacute;nh l&atilde;i, ph&iacute; l&agrave; ho&agrave;n to&agrave;n ph&ugrave; hợp với thỏa thuận giữa Eximbank v&agrave; kh&aacute;ch h&agrave;ng theo hồ sơ mở thẻ ng&agrave;y 15-3-2013 c&oacute; đầy đủ chữ k&yacute; kh&aacute;ch h&agrave;ng (quy định về ph&iacute;, l&atilde;i được quy định r&otilde; trong Biểu ph&iacute; ph&aacute;t h&agrave;nh, sử dụng thẻ đ&atilde; được đăng tải c&ocirc;ng khai tr&ecirc;n website của Eximbank).</p>',
                'status' => 0,
            ],
        ];

        foreach ($news as $item) {
            News::create($item);
        }
    }
}
