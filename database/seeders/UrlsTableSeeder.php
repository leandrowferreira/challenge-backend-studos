<?php

namespace Database\Seeders;

use App\Models\Url;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UrlsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('urls')->insert([
            'slug'       => 'abc123ab',
            'url'        => 'studos.com.br',
            'valid'      => Carbon::now()->addMonths(3),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        Url::factory()->count(30)->create();
    }
}
