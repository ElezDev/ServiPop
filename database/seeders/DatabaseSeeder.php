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
        
        // Primero crear los roles
        $this->call([
            RoleSeeder::class, // Aquí llamas a tu RoleSeeder
        ]);

        $this->call([
            CategoriesTableSeeder::class,
        ]);

        User::factory()->create([
            'name' => 'Edwin',
            'email' => 'test@example.com',
        ]);
    }
}
