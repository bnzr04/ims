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
                'name' => 'Pharmacy Manager',
                'username' => 'pharmacy',
                'type' => 2,
                'dept' => 0,
                'password' => bcrypt('123456'),
            ],
            [
                'name' => 'Csr Manager',
                'username' => 'csr',
                'type' => 2,
                'dept' => 1,
                'password' => bcrypt('123456'),
            ],
            [
                'name' => 'User',
                'username' => 'user',
                'type' => 0,
                'password' => bcrypt('123456'),
            ],
        ];

        foreach ($users as $key => $user) {
            User::create($user);
        }
    }
}
