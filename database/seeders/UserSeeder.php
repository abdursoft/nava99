<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                "email" => "admin@gmail.com",
                "password" => "password",
                "phone" => "+8801892311511",
                "role" => "admin",
                "name" => "Rony",
                "user_name" => "Admin11",
                "is_verified" => "1",
                "dob" => date("Y-m-d", strtotime("-20 years"))
            ],
            [
                "email" => "user@gmail.com",
                "password" => "password",
                "phone" => "+8801892311522",
                "role" => "user",
                "name" => "Abdur",
                "user_name" => "abdurX",
                "is_verified" => "1",
                "balance" => 200.49,
                "currency" => "BDT",
                "dob" => date("Y-m-d", strtotime("-20 years"))
            ],
            [
                "email" => "agent@gmail.com",
                "password" => "password",
                "phone" => "+8801892311533",
                "role" => "agent",
                "name" => "Rahim",
                "user_name" => "Rahim3x",
                "is_verified" => "1",
                "balance" => 200.49,
                "currency" => "BDT",
                "dob" => date("Y-m-d", strtotime("-20 years"))
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
