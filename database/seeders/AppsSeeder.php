<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\App;

class AppsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        App::create([
            'name' => 'VietComBank',
            'status' => 1,
            'android_version' => 'v1.0.0',
            'ios_version' => 'v1.0.0',
            'android_download_link' => 'Đường Link',
            'ios_download_link' => 'Đường link',
        ]);
    }
}