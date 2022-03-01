<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use Illuminate\Database\Seeder;

class RestaurantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Restaurant::create([
            'title' => 'ShakesBierre',
            'phone' => '9830303',
            'opening_time' => now(),
            'closing_time' =>  now(),
            'max_table_occupancy' => '16',
            'user_id' => 1
        ]);
    }
}
