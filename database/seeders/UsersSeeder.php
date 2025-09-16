<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run()
    {
        // 1. ADMIN
        $admin = User::create([
            'name' => 'Administrador Sistema',
            'email' => 'admin@sistema.com',
            'password' => Hash::make('admin123'),
            'telefone' => '(11) 99999-0001',
            'endereco' => 'Rua Admin, 123',
            'cep' => '01000-001',
        ]);
        $admin->assignRole('admin');

        // 2. DIRETOR
        $diretor = User::create([
            'name' => 'João Silva (Diretor)',
            'email' => 'diretor@sistema.com',
            'password' => Hash::make('diretor123'),
            'telefone' => '(11) 99999-0002',
            'endereco' => 'Rua Diretor, 456',
            'cep' => '01000-002',
        ]);
        $diretor->assignRole('diretor');

        // 3. COMERCIAIS
        $comercial1 = User::create([
            'name' => 'Maria Santos (Comercial)',
            'email' => 'maria@sistema.com',
            'password' => Hash::make('maria123'),
            'telefone' => '(11) 99999-0003',
            'endereco' => 'Rua Comercial, 789',
            'cep' => '01000-003',
        ]);
        $comercial1->assignRole('comercial');

        $comercial2 = User::create([
            'name' => 'Pedro Oliveira (Comercial)',
            'email' => 'pedro@sistema.com',
            'password' => Hash::make('pedro123'),
            'telefone' => '(11) 99999-0004',
            'endereco' => 'Rua Vendas, 101',
            'cep' => '01000-004',
        ]);
        $comercial2->assignRole('comercial');

        // 4. VISITANTE (para futuro)
        $visitante = User::create([
            'name' => 'Ana Costa (Visitante)',
            'email' => 'visitante@sistema.com',
            'password' => Hash::make('visitante123'),
            'telefone' => '(11) 99999-0005',
            'endereco' => 'Rua Visitante, 202',
            'cep' => '01000-005',
        ]);
        $visitante->assignRole('visitante');

        $this->command->info('Usuários criados com sucesso!');
        $this->command->table(
            ['Role', 'Nome', 'Email', 'Senha'],
            [
                ['admin', 'Administrador Sistema', 'admin@sistema.com', 'admin123'],
                ['diretor', 'João Silva (Diretor)', 'diretor@sistema.com', 'diretor123'],
                ['comercial', 'Maria Santos (Comercial)', 'maria@sistema.com', 'maria123'],
                ['comercial', 'Pedro Oliveira (Comercial)', 'pedro@sistema.com', 'pedro123'],
                ['visitante', 'Ana Costa (Visitante)', 'visitante@sistema.com', 'visitante123'],
            ]
        );
    }
}