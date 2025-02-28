<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin12345'),
            'role' => 'admin',
        ];
        DB::table('users')->insert($param);

        $param = [
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => Hash::make('user12345'),
            'role' => 'user',
        ];
        DB::table('users')->insert($param);
    }
}
