<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'username' => 'demoadmin01',
            'password' => Hash::make('demoadmin01'),
            'fullname' => 'demoadmin01username',
            'email' => 'demoadmin01@gmail.com',
            'phonenumber' => Str::random(13),
            'alamat' => 'Admin Street',
            'profilepicture' => 'default.jpg',
            'role' => 'Admin',
        ]);
    }
}