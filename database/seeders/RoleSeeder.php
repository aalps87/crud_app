<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin não recebe permissões: o Gate::before do AppServiceProvider libera tudo.
        Role::firstOrCreate(['name' => 'Super Admin']);

        Role::firstOrCreate(['name' => 'Admin'])->syncPermissions([
            'criar-usuario',
            'editar-usuario',
            'excluir-usuario',
            'criar-aeronave',
            'editar-aeronave',
            'excluir-aeronave',
        ]);

        Role::firstOrCreate(['name' => 'Gerente de Frota'])->syncPermissions([
            'criar-aeronave',
            'editar-aeronave',
            'excluir-aeronave',
        ]);

        Role::firstOrCreate(['name' => 'Usuário'])->syncPermissions([
            'ver-aeronave',
        ]);
    }
}
