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
                'title' => 'Tiêu đề bài viết 1',
                'content' => 'Nội dung bài viết 1',
                'status' => 0,
            ],
            [
                'title' => 'Tiêu đề bài viết 2',
                'content' => 'Nội dung bài viết 2',
                'status' => 0,
            ],
            [
                'title' => 'Tiêu đề bài viết 3',
                'content' => 'Nội dung bài viết 3',
                'status' => 0,
            ],
        ];

        foreach ($news as $item) {
            News::create($item);
        }
    }
}
