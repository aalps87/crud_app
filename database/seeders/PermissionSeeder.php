<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'criar-papel',
            'editar-papel',
            'excluir-papel',
            'criar-usuario',
            'editar-usuario',
            'excluir-usuario',
            'ver-aeronave',
            'criar-aeronave',
            'editar-aeronave',
            'excluir-aeronave',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
