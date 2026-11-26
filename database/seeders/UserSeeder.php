<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            [
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => 'admin1234',
                'role' => 'admin',
            ],
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'password' => 'admin1234',
                'role' => 'admin',
            ],
            [
                'name' => 'Student',
                'email' => 'student@example.com',
                'password' => 'demo1234',
                'role' => 'user',
            ],
            [
                'name' => 'Staff Member',
                'email' => 'staff@example.com',
                'password' => 'staff1234',
                'role' => 'user',
            ],
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password',
                'role' => 'user',
            ],
        ];

        foreach ($accounts as $account) {
            $user = User::firstOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make($account['password']),
                ]
            );

            $user->forceFill(['role' => $account['role']])->save();
        }
    }
}
