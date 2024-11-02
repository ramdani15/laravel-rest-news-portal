<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = 'password';

        /** Create Admin */
        $adminRole = Role::firstWhere('name', 'admin');
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@news-portal.com',
            'email_verified_at' => time(),
            'password' => bcrypt($password),
        ]);
        $admin->attachRole($adminRole);

        /** Create Users */
        $userRole = Role::firstWhere('name', 'user');
        $users = User::factory(4)->create([
            'email_verified_at' => time(),
            'password' => bcrypt($password),
        ]);
        foreach ($users as $user) {
            $user->attachRole($userRole);
        }
    }
}
