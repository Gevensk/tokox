<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin TokoX',
            'username' => 'admin',
            'email' => 'admin@tokox.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Staff Produksi',
            'username' => 'staff',
            'email' => 'staff@tokox.com',
            'password' => bcrypt('staff123'),
            'role' => 'user',
        ]);
    }
}
