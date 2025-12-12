<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'first_name' => 'Admin',
                'last_name' => 'Système',
                'email' => 'admin@tickettheatre.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone_number' => '0612345678',
                'sex' => 'M',
                'date_of_birth' => '1990-01-01',
                'is_active' => true,
                'avatar' => 'https://ui-avatars.com/api/?name=Admin+Systeme',
            ],
            [
                'first_name' => 'Jean',
                'last_name' => 'Dupont',
                'email' => 'jean.dupont@example.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'phone_number' => '0623456789',
                'sex' => 'M',
                'date_of_birth' => '1985-05-15',
                'is_active' => true,
                'avatar' => 'https://ui-avatars.com/api/?name=Jean+Dupont',
                'preferences' => [
                    'newsletter' => true,
                    'notifications' => true,
                    'language' => 'fr',
                ],
            ],
            [
                'first_name' => 'Marie',
                'last_name' => 'Martin',
                'email' => 'marie.martin@example.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'phone_number' => '0634567890',
                'sex' => 'F',
                'date_of_birth' => '1992-08-22',
                'is_active' => true,
                'avatar' => 'https://ui-avatars.com/api/?name=Marie+Martin',
                'preferences' => [
                    'newsletter' => true,
                    'notifications' => false,
                    'language' => 'fr',
                ],
            ],
            [
                'first_name' => 'Pierre',
                'last_name' => 'Dubois',
                'email' => 'pierre.dubois@example.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'phone_number' => '0645678901',
                'sex' => 'M',
                'date_of_birth' => '1988-03-10',
                'is_active' => true,
                'avatar' => 'https://ui-avatars.com/api/?name=Pierre+Dubois',
            ],
            [
                'first_name' => 'Sophie',
                'last_name' => 'Bernard',
                'email' => 'sophie.bernard@example.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'phone_number' => '0656789012',
                'sex' => 'F',
                'date_of_birth' => '1995-11-30',
                'is_active' => true,
                'avatar' => 'https://ui-avatars.com/api/?name=Sophie+Bernard',
                'preferences' => [
                    'newsletter' => false,
                    'notifications' => true,
                    'language' => 'fr',
                ],
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
