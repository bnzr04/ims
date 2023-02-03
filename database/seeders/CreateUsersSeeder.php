<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class CreateUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'name' => 'Admin User 1',
                'username' => 'admin',
                'type' => 1,
                'password' => bcrypt('123456'),
            ],
            [
                'name' => 'Manager User',
                'username' => 'manager',
                'type' => 2,
                'password' => bcrypt('123456'),
            ],
            [
                'name' => 'User',
                'username' => 'user',
                'type' => 0,
                'password' => bcrypt('123456'),
            ],
            [
                'name' => 'Admin User 2',
                'username' => 'admin1',
                'type' => 1,
                'password' => bcrypt('123456'),
            ],
        ];

        foreach ($users as $key => $user) {
            User::create($user);
        }
    }
}
