<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Admin-ShakesBierre',
            'email' => 'shakesbierre@gmail.com',
            'phone' => '98330303',
            'user_type' => 2,
            'password' => Hash::make('password')
        ]);
    }
}
