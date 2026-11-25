<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            DefaultUserSeeder::class,
        ]);

        // Dados de demonstração ficam fora dos testes, que partem de um inventário vazio.
        if (! app()->runningUnitTests()) {
            $this->call(AeronaveSeeder::class);
        }
    }
}
