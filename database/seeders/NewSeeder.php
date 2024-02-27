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
                'title' => 'Vì sao giá USD tự do tăng mạnh?1',
                'content' => 'Ngày 25/2, giá USD trên thị trường tự do tiếp tục tăng mạnh thêm khoảng 50-70 đồng so với hôm qua. Cụ thể, giá mua vào USD hiện khoảng 25.200-25.220 đồng, giá bán ra khoảng 25.290-25.300 đồng. Như vậy trong tuần này (19-25/2), giá USD trên thị trường phi chính thức đã tăng khoảng 200-250 đồng.  1',
                'status' => 0,
            ],
            [
                'title' => 'Vì sao giá USD tự do tăng mạnh?2',
                'content' => 'Ngày 25/2, giá USD trên thị trường tự do tiếp tục tăng mạnh thêm khoảng 50-70 đồng so với hôm qua. Cụ thể, giá mua vào USD hiện khoảng 25.200-25.220 đồng, giá bán ra khoảng 25.290-25.300 đồng. Như vậy trong tuần này (19-25/2), giá USD trên thị trường phi chính thức đã tăng khoảng 200-250 đồng.  2',
                'status' => 0,
            ],
            [
                'title' => 'Vì sao giá USD tự do tăng mạnh?3',
                'content' => 'Ngày 25/2, giá USD trên thị trường tự do tiếp tục tăng mạnh thêm khoảng 50-70 đồng so với hôm qua. Cụ thể, giá mua vào USD hiện khoảng 25.200-25.220 đồng, giá bán ra khoảng 25.290-25.300 đồng. Như vậy trong tuần này (19-25/2), giá USD trên thị trường phi chính thức đã tăng khoảng 200-250 đồng.  3',
                'status' => 0,
            ],
        ];

        foreach ($news as $item) {
            News::create($item);
        }
    }
}
