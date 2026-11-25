<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DefaultUserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Administrador', 'email' => 'adm@gmail.com', 'password' => 'adm123', 'role' => 'Super Admin'],
            ['name' => 'Ana Souza', 'email' => 'admin@aeris.com', 'password' => 'admin1234', 'role' => 'Admin'],
            ['name' => 'Carlos Lima', 'email' => 'gerente@aeris.com', 'password' => 'gerente1234', 'role' => 'Gerente de Frota'],
            ['name' => 'Beatriz Rocha', 'email' => 'usuario@aeris.com', 'password' => 'usuario1234', 'role' => 'Usuário'],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'], 'password' => $data['password']],
            )->syncRoles([$data['role']]);
        }
    }
}
