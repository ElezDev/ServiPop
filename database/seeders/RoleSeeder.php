<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Crear roles
        Role::firstOrCreate([
            'name' => 'user',
            'guard_name' => 'web'
        ]);

        Role::firstOrCreate([
            'name' => 'serviceProvider',
            'guard_name' => 'web'
        ]);

        $this->command->info('Roles "user" y "service provider" creados exitosamente!');
    }
}